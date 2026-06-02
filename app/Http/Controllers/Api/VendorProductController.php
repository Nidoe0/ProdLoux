<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendorProductController extends Controller
{
    /**
     * Get the authenticated seller, or null if admin.
     */
    private function getSellerOrFail(): ?Seller
    {
        if (auth()->user()->isAdmin()) {
            return null;
        }
        return Seller::where('user_id', auth()->id())->firstOrFail();
    }

    /**
     * Ensure the authenticated user can touch this product.
     */
    private function authorizeProduct(Product $product, ?Seller $seller): void
    {
        if ($seller && $product->seller_id !== $seller->id) {
            abort(403, 'Vous ne pouvez pas modifier ce produit.');
        }
    }

    // GET /api/vendor/products
    public function index()
    {
        $seller = $this->getSellerOrFail();

        $products = ($seller
            ? Product::where('seller_id', $seller->id)
            : Product::query()
        )
            ->with('category:id,name', 'seller:id,shop_name')
            ->get()
            ->map(fn ($p) => array_merge($p->toArray(), ['images' => $p->images_urls]));

        return response()->json($products);
    }

    // GET /api/vendor/products/{product}
    public function show(Product $product)
    {
        $seller = $this->getSellerOrFail();
        $this->authorizeProduct($product, $seller);

        $product->load('category:id,name', 'seller:id,shop_name');
        $product->images = $product->images_urls;

        return response()->json($product);
    }

    // POST /api/vendor/products
    public function store(Request $request)
    {
        $seller = $this->getSellerOrFail();

        $rules = [
            'name'        => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'description' => 'nullable|string|max:2000',
            'latitude'    => 'nullable|numeric|between:-90,90',
            'longitude'   => 'nullable|numeric|between:-180,180',
            'images'      => 'nullable|array|max:5',
            'images.*'    => 'image|mimes:jpeg,png,webp,jpg|max:3072',
        ];

        if (! $seller) {
            $rules['seller_id'] = 'required|exists:sellers,id';
        }

        $validated = $request->validate($rules);

        $product = DB::transaction(function () use ($request, $seller, $validated) {
            $p = Product::create([
                'seller_id'   => $seller ? $seller->id : $request->seller_id,
                'category_id' => $validated['category_id'],
                'name'        => $validated['name'],
                'description' => $validated['description'] ?? null,
                'price'       => $validated['price'],
                'stock'       => $validated['stock'],
                'latitude'    => $validated['latitude']  ?? $seller?->latitude,
                'longitude'   => $validated['longitude'] ?? $seller?->longitude,
            ]);

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $p->addMedia($image)->toMediaCollection('images');
                }
            }

            return $p;
        });

        $product->images = $product->fresh()->images_urls;

        return response()->json(['message' => 'Produit créé.', 'product' => $product], 201);
    }

    // PUT /api/vendor/products/{product}
    public function update(Request $request, Product $product)
    {
        $seller = $this->getSellerOrFail();
        $this->authorizeProduct($product, $seller);

        $validated = $request->validate([
            'name'               => 'sometimes|required|string|max:255',
            'category_id'        => 'sometimes|required|exists:categories,id',
            'price'              => 'sometimes|required|numeric|min:0',
            'stock'              => 'sometimes|required|integer|min:0',
            'description'        => 'nullable|string|max:2000',
            'latitude'           => 'nullable|numeric|between:-90,90',
            'longitude'          => 'nullable|numeric|between:-180,180',
            'images'             => 'nullable|array|max:5',
            'images.*'           => 'image|mimes:jpeg,png,webp,jpg|max:3072',
            'delete_image_ids'   => 'nullable|array',
            'delete_image_ids.*' => 'integer',
        ]);

        $product->update(array_filter(
            $request->only('name', 'category_id', 'price', 'stock', 'description', 'latitude', 'longitude'),
            fn ($v) => ! is_null($v)
        ));

        // Delete specific images
        foreach ($request->input('delete_image_ids', []) as $mediaId) {
            $media = $product->media()->find($mediaId);
            if ($media) {
                $media->delete();
            }
        }

        // Add new images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $product->addMedia($image)->toMediaCollection('images');
            }
        }

        $product->images = $product->fresh()->images_urls;

        return response()->json(['message' => 'Produit mis à jour.', 'product' => $product]);
    }

    // DELETE /api/vendor/products/{product}
    public function destroy(Product $product)
    {
        $seller = $this->getSellerOrFail();
        $this->authorizeProduct($product, $seller);

        $product->clearMediaCollection('images');
        $product->delete();

        return response()->json(['message' => 'Produit supprimé.']);
    }
}
