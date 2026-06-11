<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductController extends Controller
{
    /**
     * GET /api/products
     * Params: category_id, latitude, longitude, radius (km), per_page
     */
    public function index(Request $request)
    {
        $query = Product::with(['seller:id,shop_name,latitude,longitude', 'category:id,name'])
            ->where('stock', '>', 0);

        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->latitude && $request->longitude) {
            $lat    = (float) $request->latitude;
            $lng    = (float) $request->longitude;
            $radius = (float) ($request->radius ?? 10);

            // SQLite often lacks trig functions (radians, sin, cos, acos).
            // Fall back to a bounding-box + PHP haversine filter when using sqlite.
            if (DB::getDriverName() === 'sqlite') {
                $deg = $radius / 111; // ~111 km per degree latitude
                $minLat = $lat - $deg; $maxLat = $lat + $deg;
                $minLng = $lng - $deg; $maxLng = $lng + $deg;

                $candidates = $query->whereBetween('latitude', [$minLat, $maxLat])
                                    ->whereBetween('longitude', [$minLng, $maxLng])
                                    ->get()
                                    ->map(function ($p) use ($lat, $lng) {
                                        $p->distance = $this->haversineDistance($lat, $lng, (float)$p->latitude, (float)$p->longitude);
                                        return $p;
                                    })->filter(fn($p) => $p->distance <= $radius)
                                      ->sortBy('distance')
                                      ->values();

                $perPage = (int) ($request->per_page ?? 20);
                $page = (int) max(1, $request->get('page', 1));
                $total = $candidates->count();
                $items = $candidates->forPage($page, $perPage);

                $products = new LengthAwarePaginator($items, $total, $perPage, $page, [
                    'path' => $request->url(), 'query' => $request->query(),
                ]);
            } else {
                $query->selectRaw(
                    "products.*, (6371 * acos(cos(radians(?))
                     * cos(radians(latitude))
                     * cos(radians(longitude) - radians(?))
                     + sin(radians(?)) * sin(radians(latitude)))) AS distance",
                    [$lat, $lng, $lat]
                )->having('distance', '<=', $radius)->orderBy('distance');

                $products = $query->paginate($request->per_page ?? 20);
            }
        } else {
            $query->latest();
            $products = $query->paginate($request->per_page ?? 20);
        }

        // Append media URLs and avg rating
        $products->getCollection()->transform(function ($p) {
            $p->images    = $p->images_urls;
            $p->avg_rating = $p->averageRating();
            return $p;
        });

        return response()->json($products);
    }

    /**
     * GET /api/products/{id}
     */
    public function show(Product $product)
    {
        $product->load(['seller:id,shop_name,latitude,longitude,address', 'category:id,name']);
        $product->images      = $product->images_urls;
        $product->avg_rating  = $product->averageRating();
        $product->reviews     = $product->reviews()->approved()->with('user:id,name')->latest()->take(10)->get();

        return response()->json($product);
    }

    /**
     * Compute haversine distance (km) between two lat/lng points.
     */
    protected function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    /**
     * GET /api/categories
     */
    public function categories()
    {
        return response()->json(Category::all());
    }
}
