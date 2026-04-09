<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentSchedule;
use App\Models\Product;
use App\Services\CreditCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function index()
    {
        $cart = session('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $items = [];
        $totalDeposit = 0;
        $totalFull = 0;
        $calculator = new CreditCalculator();

        foreach ($cart as $key => $item) {
            $product = Product::find($item['product_id']);
            if (!$product) continue;

            if ($item['payment_type'] === 'credit') {
                $credit = $calculator->calculate(
                    $product->price,
                    (int) $item['credit_months'],
                    (float) $item['interest_rate']
                );
                $totalDeposit += $credit['deposit'] * $item['quantity'];
                $totalFull    += $credit['total_payable'] * $item['quantity'];
                $items[] = array_merge($item, ['product' => $product, 'cart_key' => $key, 'credit' => $credit]);
            } else {
                $totalDeposit += $product->price * $item['quantity'];
                $totalFull    += $product->price * $item['quantity'];
                $items[] = array_merge($item, ['product' => $product, 'cart_key' => $key, 'credit' => null]);
            }
        }

        return view('checkout.index', compact('items', 'totalDeposit', 'totalFull'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:100',
            'phone'          => 'required|string|max:20',
            'address'        => 'required|string|max:255',
            'city'           => 'required|string|max:100',
            'payment_method' => 'required|in:cash_on_delivery,mobile_money',
            'mobile_number'  => 'nullable|required_if:payment_method,mobile_money|string|max:20',
        ]);

        $cart = session('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index');
        }

        $calculator = new CreditCalculator();
        $totalDeposit = 0;
        $totalFull = 0;

        $order = Order::create([
            'order_number'    => 'ORD-' . strtoupper(Str::random(8)),
            'user_id'         => auth()->id(),
            'status'          => 'pending',
            'total_deposit'   => 0,
            'total_full'      => 0,
            'payment_method'  => $request->payment_method,
            'shipping_address' => [
                'name'    => $request->name,
                'phone'   => $request->phone,
                'address' => $request->address,
                'city'    => $request->city,
            ],
            'notes' => $request->notes,
        ]);

        foreach ($cart as $item) {
            $product = Product::find($item['product_id']);
            if (!$product) continue;

            $orderItem = OrderItem::create([
                'order_id'      => $order->id,
                'product_id'    => $product->id,
                'product_title' => $product->title,
                'quantity'      => $item['quantity'],
                'unit_price'    => $product->price,
                'payment_type'  => $item['payment_type'],
                'credit_months' => $item['credit_months'] ?? null,
                'interest_rate' => $item['interest_rate'] ?? null,
            ]);

            if ($item['payment_type'] === 'credit') {
                $credit = $calculator->calculate(
                    $product->price * $item['quantity'],
                    (int) $item['credit_months'],
                    (float) $item['interest_rate']
                );

                foreach ($credit['schedule'] as $slot) {
                    PaymentSchedule::create([
                        'order_id'          => $order->id,
                        'order_item_id'     => $orderItem->id,
                        'due_date'          => $slot['due_date'],
                        'amount'            => $slot['amount'],
                        'principal'         => $slot['principal'],
                        'interest'          => $slot['interest'],
                        'remaining_balance' => $slot['remaining_balance'],
                    ]);
                }

                $totalDeposit += $credit['deposit'];
                $totalFull    += $credit['total_payable'];
            } else {
                $totalDeposit += $product->price * $item['quantity'];
                $totalFull    += $product->price * $item['quantity'];
            }
        }

        $order->update(['total_deposit' => $totalDeposit, 'total_full' => $totalFull]);

        session()->forget('cart');

        return redirect()->route('orders.show', $order)->with('success', 'Order placed successfully!');
    }
}
