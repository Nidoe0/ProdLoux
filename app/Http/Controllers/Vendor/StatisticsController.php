<?php

namespace App\Http\Controllers\Vendor;

use App\Exports\OrdersExport;
use App\Http\Controllers\Controller;
use App\Models\Seller;
use App\Services\StatisticsService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class StatisticsController extends Controller
{
    public function __construct(private StatisticsService $stats) {}

    public function index(Request $request)
    {
        $period = $request->get('period', 'month');

        if (auth()->user()->isAdmin()) {
            $data = $this->stats->admin($period);
            return view('vendor.statistics.admin', compact('data', 'period'));
        }

        $seller = Seller::where('user_id', auth()->id())->firstOrFail();
        $data   = $this->stats->forSeller($seller, $period);

        return view('vendor.statistics.index', compact('data', 'period', 'seller'));
    }

    public function exportOrders(Request $request)
    {
        $request->validate([
            'from' => 'required|date',
            'to'   => 'required|date|after_or_equal:from',
        ]);

        $seller = auth()->user()->isAdmin()
            ? null
            : Seller::where('user_id', auth()->id())->firstOrFail();

        $filename = 'commandes_' . $request->from . '_' . $request->to . '.xlsx';

        return Excel::download(
            new OrdersExport($seller, $request->from, $request->to),
            $filename
        );
    }
}
