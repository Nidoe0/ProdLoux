<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    private function getSeller(): ?Seller
    {
        if (auth()->user()->isAdmin()) return null;
        return Seller::where('user_id', auth()->id())->firstOrFail();
    }

    public function index()
    {
        $seller   = $this->getSeller();
        $products = ($seller
            ? Product::with(['category', 'media'])->where('seller_id', $seller->id)
            : Product::with(['category', 'seller', 'media'])
        )->latest()->paginate(20);

        return view('vendor.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::all();
        $sellers    = auth()->user()->isAdmin() ? Seller::with('user')->get() : collect();
        return view('vendor.products.create', compact('categories', 'sellers'));
    }

    public function store(Request $request)
    {
        $seller = $this->getSeller();

        $rules = [
            'name'        => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'description' => 'nullable|string|max:2000',
            'images'      => 'nullable|array|max:5',
            'images.*'    => 'image|mimes:jpeg,png,webp|max:3072',
            'latitude'    => 'nullable|numeric',
            'longitude'   => 'nullable|numeric',
        ];

        if (!$seller) $rules['seller_id'] = 'required|exists:sellers,id';

        $request->validate($rules);

        DB::transaction(function () use ($request, $seller) {
            $p = Product::create([
                'seller_id'   => $seller ? $seller->id : $request->seller_id,
                'category_id' => $request->category_id,
                'name'        => $request->name,
                'description' => $request->description,
                'price'       => $request->price,
                'stock'       => $request->stock,
                'latitude'    => $request->latitude  ?? optional($seller)->latitude,
                'longitude'   => $request->longitude ?? optional($seller)->longitude,
            ]);

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $img) {
                    $p->addMedia($img)->toMediaCollection('images');
                }
            }
        });

        return redirect()->route('vendor.products.index')->with('success', 'Produit ajouté avec succès.');
    }

    public function edit(Product $product)
    {
        $seller = $this->getSeller();
        if ($seller) abort_if($product->seller_id !== $seller->id, 403);

        $categories = Category::all();
        $mediaItems = $product->getMedia('images');

        return view('vendor.products.edit', compact('product', 'categories', 'mediaItems'));
    }

    public function update(Request $request, Product $product)
    {
        $seller = $this->getSeller();
        if ($seller) abort_if($product->seller_id !== $seller->id, 403);

        $request->validate([
            'name'        => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'description' => 'nullable|string|max:2000',
            'images'      => 'nullable|array|max:5',
            'images.*'    => 'image|mimes:jpeg,png,webp|max:3072',
            'latitude'    => 'nullable|numeric',
            'longitude'   => 'nullable|numeric',
            'delete_media_ids' => 'nullable|array',
            'delete_media_ids.*' => 'integer',
        ]);

        $product->update($request->only('name','category_id','price','stock','description','latitude','longitude'));

        if ($request->delete_media_ids) {
            foreach ($request->delete_media_ids as $mediaId) {
                $media = $product->media()->find($mediaId);
                if ($media) $media->delete();
            }
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $img) {
                $product->addMedia($img)->toMediaCollection('images');
            }
        }

        return redirect()->route('vendor.products.index')->with('success', 'Produit mis à jour.');
    }

    public function destroy(Product $product)
    {
        $seller = $this->getSeller();
        if ($seller) abort_if($product->seller_id !== $seller->id, 403);

        $product->clearMediaCollection('images');
        $product->delete();

        return redirect()->route('vendor.products.index')->with('success', 'Produit supprimé.');
    }
}
