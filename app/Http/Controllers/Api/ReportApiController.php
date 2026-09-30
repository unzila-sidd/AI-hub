<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportApiController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('reports.view'), 403);

        $from = $request->date('from') ? $request->date('from')->startOfDay() : now()->startOfMonth();
        $to = $request->date('to') ? $request->date('to')->endOfDay() : now()->endOfDay();

        $salesQuery = Sale::betweenDates($from, $to);

        $summary = [
            'sales' => (clone $salesQuery)->count(),
            'revenue' => (float) (clone $salesQuery)->sum('total'),
            'items_sold' => (int) SaleItem::whereHas('sale', fn ($q) => $q->betweenDates($from, $to))->sum('qty'),
            'avg' => (float) (clone $salesQuery)->avg('total'),
        ];

        $daily = (clone $salesQuery)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as sales, SUM(total) as revenue')
            ->groupBy('date')
            ->orderByDesc('date')
            ->limit(30)
            ->get();

        $topProducts = SaleItem::select('product_name', DB::raw('SUM(qty) as qty'), DB::raw('SUM(line_total) as revenue'))
            ->whereHas('sale', fn ($q) => $q->betweenDates($from, $to))
            ->groupBy('product_name')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        $byMethod = Payment::select('method', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->whereHas('sale', fn ($q) => $q->betweenDates($from, $to))
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('method')
            ->orderByDesc('total')
            ->get();

        $lowStock = Product::lowStock()->active()->orderBy('stock')->limit(10)->get();

        return response()->json([
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'summary' => $summary,
            'daily' => $daily,
            'top_products' => $topProducts,
            'by_method' => $byMethod,
            'low_stock' => $lowStock,
        ]);
    }
}