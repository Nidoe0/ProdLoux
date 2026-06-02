<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Seller;

class OrderController extends Controller
{
    public function index()
    {
        if (auth()->user()->isAdmin()) {
            $orders = Order::with(['user', 'items.product'])->latest()->get();
        } else {
            $seller           = Seller::where('user_id', auth()->id())->firstOrFail();
            $sellerProductIds = Product::where('seller_id', $seller->id)->pluck('id');
            $orders           = Order::with(['user', 'items.product'])
                ->whereHas('items', fn($q) => $q->whereIn('product_id', $sellerProductIds))
                ->latest()->get();
        }

        return view('vendor.orders.index', compact('orders'));
    }

    public function updateStatus(Order $order, string $status)
    {
        if (!auth()->user()->isAdmin()) {
            $seller           = Seller::where('user_id', auth()->id())->firstOrFail();
            $sellerProductIds = Product::where('seller_id', $seller->id)->pluck('id');
            abort_if(!$order->items()->whereIn('product_id', $sellerProductIds)->exists(), 403);
        }

        $allowed = ['pending', 'confirmed', 'delivered', 'cancelled'];
        abort_if(!in_array($status, $allowed), 422, 'Statut invalide.');

        $order->update(['status' => $status]);
        return redirect()->route('vendor.orders.index')->with('success', 'Statut mis à jour.');
    }
}
