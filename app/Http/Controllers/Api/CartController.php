<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $items = Cart::with(['product.seller:id,shop_name'])
            ->where('user_id', $request->user()->id)
            ->get()
            ->map(function ($item) {
                $item->product_price  = $item->product?->price;
                $item->subtotal       = $item->product?->price * $item->quantity;
                $item->product_images = $item->product?->images_urls ?? [];
                return $item;
            });

        $total = $items->sum('subtotal');

        return response()->json(['items' => $items, 'total' => $total]);
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);
        abort_if($product->stock < $request->quantity, 422, "Stock insuffisant (disponible : {$product->stock}).");

        $cart = Cart::updateOrCreate(
            ['user_id' => $request->user()->id, 'product_id' => $request->product_id],
            ['quantity' => $request->quantity]
        );

        return response()->json(['message' => 'Produit ajouté au panier.', 'cart' => $cart], 201);
    }

    public function update(Request $request, Cart $cart)
    {
        abort_if($cart->user_id !== $request->user()->id, 403);
        $request->validate(['quantity' => 'required|integer|min:1']);

        $cart->update(['quantity' => $request->quantity]);

        return response()->json(['message' => 'Quantité mise à jour.', 'cart' => $cart]);
    }

    public function remove(Request $request, Cart $cart)
    {
        abort_if($cart->user_id !== $request->user()->id, 403);
        $cart->delete();

        return response()->json(['message' => 'Article retiré du panier.']);
    }

    public function clear(Request $request)
    {
        Cart::where('user_id', $request->user()->id)->delete();

        return response()->json(['message' => 'Panier vidé.']);
    }
}
