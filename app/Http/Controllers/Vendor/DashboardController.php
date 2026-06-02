<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Seller;

class DashboardController extends Controller
{
    public function index()
    {
        // Admin voit tout, seller voit ses propres données
        if (auth()->user()->isAdmin()) {
            $totalProducts = Product::count();
            $totalOrders   = Order::count();
            $totalRevenue  = Order::where('status', 'confirmed')->sum('total');
            $recentOrders  = Order::with('user')->latest()->take(6)->get();
        } else {
            $seller = Seller::where('user_id', auth()->id())->firstOrFail();
            $sellerProductIds = Product::where('seller_id', $seller->id)->pluck('id');

            $totalProducts = Product::where('seller_id', $seller->id)->count();
            $totalOrders   = Order::whereHas('items', fn($q) => $q->whereIn('product_id', $sellerProductIds))->count();
            $totalRevenue  = Order::where('status', 'confirmed')
                ->whereHas('items', fn($q) => $q->whereIn('product_id', $sellerProductIds))
                ->sum('total');
            $recentOrders  = Order::with(['user', 'items.product'])
                ->whereHas('items', fn($q) => $q->whereIn('product_id', $sellerProductIds))
                ->latest()->take(6)->get();
        }

        return view('vendor.dashboard.index', compact(
            'totalProducts', 'totalOrders', 'totalRevenue', 'recentOrders'
        ));
    }
}
