<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Seller;
use Illuminate\Support\Carbon;

class StatisticsService
{
    /**
     * Full statistics for a single seller.
     */
    public function forSeller(Seller $seller, string $period = 'month'): array
    {
        [$from, $to] = $this->periodDates($period);

        $productIds = Product::where('seller_id', $seller->id)->pluck('id');

        $revenue = Payment::where('seller_id', $seller->id)
            ->where('status', 'transferred')
            ->whereBetween('created_at', [$from, $to])
            ->sum('seller_amount');

        $totalOrders = Order::whereHas('items', fn ($q) => $q->whereIn('product_id', $productIds))
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $revenueByDay = Payment::where('seller_id', $seller->id)
            ->where('status', 'transferred')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, SUM(seller_amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('total', 'date');

        $topProducts = OrderItem::whereIn('product_id', $productIds)
            ->selectRaw('product_id, SUM(quantity) as total_qty, SUM(quantity * price) as total_revenue')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->with('product:id,name,stock')
            ->take(5)
            ->get();

        $orderStatuses = Order::whereHas('items', fn ($q) => $q->whereIn('product_id', $productIds))
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $lowStockProducts = Product::where('seller_id', $seller->id)
            ->where('stock', '<=', config('marketplace.low_stock_threshold', 5))
            ->get(['id', 'name', 'stock']);

        $returns = Order::whereHas('items', fn ($q) => $q->whereIn('product_id', $productIds))
            ->where('status', 'cancelled')
            ->whereBetween('created_at', [$from, $to])
            ->count();

        return compact(
            'revenue', 'totalOrders', 'revenueByDay',
            'topProducts', 'orderStatuses', 'lowStockProducts',
            'returns', 'from', 'to', 'period'
        );
    }

    /**
     * Platform-wide statistics (admin only).
     */
    public function admin(string $period = 'month'): array
    {
        [$from, $to] = $this->periodDates($period);

        $totalRevenue    = Payment::where('status', 'transferred')->whereBetween('created_at', [$from, $to])->sum('seller_amount');
        $totalCommission = Payment::where('status', 'transferred')->whereBetween('created_at', [$from, $to])->sum('commission_amount');
        $totalOrders     = Order::whereBetween('created_at', [$from, $to])->count();
        $totalSellers    = Seller::count();
        $totalProducts   = Product::count();

        $revenueByDay = Payment::where('status', 'transferred')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, SUM(seller_amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('total', 'date');

        $topSellers = Seller::with('user:id,name')
            ->withSum(
                ['payments as total_revenue' => fn ($q) => $q->where('status', 'transferred')],
                'seller_amount'
            )
            ->orderByDesc('total_revenue')
            ->take(5)
            ->get();

        return compact(
            'totalRevenue', 'totalCommission', 'totalOrders',
            'totalSellers', 'totalProducts', 'revenueByDay', 'topSellers',
            'from', 'to', 'period'
        );
    }

    /**
     * Returns [Carbon $from, Carbon $to] for the given period string.
     */
    private function periodDates(string $period): array
    {
        return match ($period) {
            'week'  => [Carbon::now()->startOfWeek(),  Carbon::now()->endOfDay()],
            'year'  => [Carbon::now()->startOfYear(),  Carbon::now()->endOfDay()],
            'month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfDay()],
            default => [Carbon::now()->subDays(30),    Carbon::now()->endOfDay()],
        };
    }
}
