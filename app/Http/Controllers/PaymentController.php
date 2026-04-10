<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PaymentSchedule;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function show(Order $order, PaymentSchedule $schedule)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }
        $order->load('items.product');
        return view('payments.show', compact('order', 'schedule'));
    }

    public function process(Request $request, Order $order, PaymentSchedule $schedule)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'amount' => 'required|integer|min:1',
        ]);

        if ($schedule->paid) {
            return redirect()->back()->with('error', 'This installment is already paid.');
        }

        $schedule->update([
            'paid'        => true,
            'paid_date'   => now()->toDateString(),
            'paid_amount' => $request->amount,
        ]);

        return redirect()->route('orders.show', $order)->with('success', 'Payment recorded successfully!');
    }
}
