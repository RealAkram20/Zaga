@extends('layouts.app')
@section('title', 'Shopping Cart')

@section('content')
<div class="container" style="padding:32px 16px;">
    <h2 style="margin-bottom:24px;">Shopping Cart</h2>

    @if(empty($items))
        <div style="text-align:center;padding:60px 0;">
            <p style="font-size:18px;color:#64748b;margin-bottom:16px;">Your cart is empty.</p>
            <a href="{{ route('shop.index') }}" class="btn-primary">Continue Shopping</a>
        </div>
    @else
        <div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;" class="cart-grid">
            <!-- Cart Items -->
            <div>
                @foreach($items as $item)
                    <div class="product-card" style="display:flex;gap:16px;margin-bottom:16px;padding:16px;align-items:center;">
                        <img src="{{ asset($item['product']->image ?? 'images/logo.png') }}" alt="{{ $item['product']->title }}"
                             style="width:80px;height:80px;object-fit:contain;border:1px solid #e2e8f0;border-radius:4px;flex-shrink:0;">
                        <div style="flex:1;">
                            <h3 style="font-size:15px;margin-bottom:4px;">{{ $item['product']->title }}</h3>
                            <p style="font-size:13px;color:#64748b;margin-bottom:4px;">
                                {{ $item['payment_type'] === 'credit' ? 'Credit — ' . $item['credit_months'] . ' months @ ' . $item['interest_rate'] . '% APR' : 'Full Payment' }}
                            </p>
                            <p style="font-weight:700;color:#2563eb;">UGX {{ number_format($item['product']->price) }}</p>
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <form method="POST" action="{{ route('cart.update', $item['cart_key']) }}">
                                @csrf @method('PATCH')
                                <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="1"
                                       style="width:60px;padding:10px 6px;border:1px solid #e2e8f0;border-radius:4px;font-size:14px;min-height:40px;"
                                       onchange="this.form.submit()">
                            </form>
                            <form method="POST" action="{{ route('cart.remove', $item['cart_key']) }}">
                                @csrf @method('DELETE')
                                <button type="submit" aria-label="Remove {{ $item['product']->title }} from cart" style="background:#dc3545;color:#fff;border:none;padding:10px 14px;border-radius:6px;cursor:pointer;font-size:14px;min-width:40px;min-height:40px;display:inline-flex;align-items:center;justify-content:center;">✕</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Order Summary -->
            <div>
                <div class="admin-card" style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:20px;">
                    <h3 style="margin-bottom:16px;">Order Summary</h3>
                    <div style="font-size:14px;">
                        <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                            <span>Items ({{ count($items) }})</span>
                            <span>UGX {{ number_format(collect($items)->sum(fn($i) => $i['product']->price * $i['quantity'])) }}</span>
                        </div>
                    </div>
                    <hr style="margin:16px 0;border:none;border-top:1px solid #e2e8f0;">
                    <a href="{{ route('checkout.index') }}" class="btn-primary" style="display:block;text-align:center;width:100%;padding:12px;">
                        Proceed to Checkout
                    </a>
                    <a href="{{ route('shop.index') }}" style="display:block;text-align:center;margin-top:12px;color:#2563eb;font-size:14px;">
                        Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
@media (max-width: 768px) {
    .cart-grid { grid-template-columns: 1fr !important; }
}
@media (max-width: 480px) {
    .cart-grid .product-card[style*="display:flex"] {
        flex-wrap: wrap !important;
    }
}
</style>
@endsection
