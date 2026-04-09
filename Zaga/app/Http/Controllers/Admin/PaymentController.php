<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentSchedule;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = PaymentSchedule::with('order.user', 'orderItem')->latest('due_date');

        if ($request->filter === 'overdue') {
            $query->where('paid', false)->where('due_date', '<', now());
        } elseif ($request->filter === 'upcoming') {
            $query->where('paid', false)->where('due_date', '>=', now());
        } elseif ($request->filter === 'paid') {
            $query->where('paid', true);
        }

        $schedules = $query->paginate(25)->withQueryString();
        return view('admin.payments.index', compact('schedules'));
    }

    public function markPaid(Request $request, PaymentSchedule $schedule)
    {
        $schedule->update([
            'paid'        => true,
            'paid_date'   => now()->toDateString(),
            'paid_amount' => $schedule->amount,
        ]);
        return redirect()->back()->with('success', 'Payment marked as paid.');
    }
}
