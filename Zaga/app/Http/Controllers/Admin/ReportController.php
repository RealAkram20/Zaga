<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentSchedule;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        $totalRevenue     = Order::sum('total_deposit');
        $outstandingCredit = PaymentSchedule::where('paid', false)->sum('amount');
        $totalCreditPaid  = PaymentSchedule::where('paid', true)->sum('paid_amount');
        $overdueCount     = PaymentSchedule::where('paid', false)->where('due_date', '<', now())->count();

        $salesByCategory = Product::join('order_items', 'products.id', '=', 'order_items.product_id')
            ->select('products.category', DB::raw('SUM(order_items.quantity) as total_sold'))
            ->groupBy('products.category')
            ->orderByDesc('total_sold')
            ->get();

        $monthlyRevenue = Order::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(total_deposit) as revenue'),
                DB::raw('COUNT(*) as orders')
            )
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->take(12)
            ->get();

        $topProducts = Product::join('order_items', 'products.id', '=', 'order_items.product_id')
            ->select('products.id', 'products.title', 'products.category', DB::raw('SUM(order_items.quantity) as total_sold'))
            ->groupBy('products.id', 'products.title', 'products.category')
            ->orderByDesc('total_sold')
            ->take(10)
            ->get();

        return view('admin.reports.index', compact(
            'totalRevenue', 'outstandingCredit', 'totalCreditPaid',
            'overdueCount', 'salesByCategory', 'monthlyRevenue', 'topProducts'
        ));
    }
}
