<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $cart = session('cart', []);
        $items = [];
        foreach ($cart as $key => $item) {
            $product = Product::find($item['product_id']);
            if ($product) {
                $items[] = array_merge($item, ['product' => $product, 'cart_key' => $key]);
            }
        }
        return view('cart.index', compact('items'));
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id'   => 'required|exists:products,id',
            'quantity'     => 'required|integer|min:1',
            'payment_type' => 'required|in:full,credit',
            'credit_months'=> 'nullable|integer|in:3,6',
            'interest_rate'=> 'nullable|numeric|in:0,5,9.99,14.99',
        ]);

        $cart = session('cart', []);
        $key = $request->product_id . '_' . $request->payment_type . '_' . ($request->credit_months ?? 0);

        $cart[$key] = [
            'product_id'    => $request->product_id,
            'quantity'      => $request->quantity,
            'payment_type'  => $request->payment_type,
            'credit_months' => $request->credit_months,
            'interest_rate' => $request->interest_rate,
        ];

        session(['cart' => $cart]);

        return redirect()->route('cart.index')->with('success', 'Item added to cart.');
    }

    public function update(Request $request, string $cartKey)
    {
        $request->validate(['quantity' => 'required|integer|min:1']);
        $cart = session('cart', []);
        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] = $request->quantity;
            session(['cart' => $cart]);
        }
        return redirect()->route('cart.index');
    }

    public function remove(string $cartKey)
    {
        $cart = session('cart', []);
        unset($cart[$cartKey]);
        session(['cart' => $cart]);
        return redirect()->route('cart.index')->with('success', 'Item removed.');
    }
}
