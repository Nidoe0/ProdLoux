<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

            $query->selectRaw(
                "products.*, (6371 * acos(cos(radians(?))
                 * cos(radians(latitude))
                 * cos(radians(longitude) - radians(?))
                 + sin(radians(?)) * sin(radians(latitude)))) AS distance",
                [$lat, $lng, $lat]
            )->having('distance', '<=', $radius)->orderBy('distance');
        } else {
            $query->latest();
        }

        $products = $query->paginate($request->per_page ?? 20);

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
     * GET /api/categories
     */
    public function categories()
    {
        return response()->json(Category::all());
    }
}
