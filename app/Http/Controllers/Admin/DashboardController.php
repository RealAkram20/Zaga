<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentSchedule;
use App\Models\Product;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_products'     => Product::count(),
            'total_customers'    => User::where('role', 'customer')->count(),
            'total_orders'       => Order::count(),
            'total_revenue'      => Order::sum('total_deposit'),
            'outstanding_credit' => PaymentSchedule::where('paid', false)->sum('amount'),
            'overdue_payments'   => PaymentSchedule::where('paid', false)->where('due_date', '<', now())->count(),
            'recent_orders'      => Order::with('user')->latest()->take(5)->get(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
