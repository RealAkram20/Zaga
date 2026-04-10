@extends('layouts.app')
@section('title', 'Checkout')

@section('content')
<div class="container" style="padding:32px 16px;">
    <h2 style="margin-bottom:24px;">Checkout</h2>

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;" class="checkout-grid">
        <!-- Shipping Form -->
        <div>
            <form method="POST" action="{{ route('checkout.store') }}">
                @csrf

                <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:24px;margin-bottom:20px;">
                    <h3 style="margin-bottom:20px;">Shipping Information</h3>

                    <div class="checkout-form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                        <div>
                            <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">Full Name *</label>
                            <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" required
                                   style="width:100%;padding:10px;border:1px solid #e2e8f0;border-radius:6px;">
                            @error('name')<p style="color:#dc3545;font-size:12px;margin-top:4px;">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">Phone *</label>
                            <input type="text" name="phone" value="{{ old('phone', auth()->user()->phone) }}" required
                                   style="width:100%;padding:10px;border:1px solid #e2e8f0;border-radius:6px;">
                            @error('phone')<p style="color:#dc3545;font-size:12px;margin-top:4px;">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div style="margin-top:16px;">
                        <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">Delivery Address *</label>
                        <input type="text" name="address" value="{{ old('address', auth()->user()->address) }}" required
                               style="width:100%;padding:10px;border:1px solid #e2e8f0;border-radius:6px;" placeholder="Street, Building, Area">
                        @error('address')<p style="color:#dc3545;font-size:12px;margin-top:4px;">{{ $message }}</p>@enderror
                    </div>

                    <div style="margin-top:16px;">
                        <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">City *</label>
                        <input type="text" name="city" value="{{ old('city', 'Kampala') }}" required
                               style="width:100%;padding:10px;border:1px solid #e2e8f0;border-radius:6px;">
                        @error('city')<p style="color:#dc3545;font-size:12px;margin-top:4px;">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:24px;margin-bottom:20px;">
                    <h3 style="margin-bottom:20px;">Payment Method</h3>

                    <label style="display:flex;align-items:center;gap:10px;padding:12px;border:1px solid #e2e8f0;border-radius:6px;cursor:pointer;margin-bottom:10px;">
                        <input type="radio" name="payment_method" value="cash_on_delivery" {{ old('payment_method','cash_on_delivery') == 'cash_on_delivery' ? 'checked' : '' }}
                               onclick="document.getElementById('mobileFields').style.display='none'">
                        <span><strong>Cash on Delivery</strong> — Pay when your order arrives</span>
                    </label>

                    <label style="display:flex;align-items:center;gap:10px;padding:12px;border:1px solid #e2e8f0;border-radius:6px;cursor:pointer;">
                        <input type="radio" name="payment_method" value="mobile_money" {{ old('payment_method') == 'mobile_money' ? 'checked' : '' }}
                               onclick="document.getElementById('mobileFields').style.display='block'">
                        <span><strong>Mobile Money</strong> — MTN or Airtel Money</span>
                    </label>

                    <div id="mobileFields" style="display:none;margin-top:16px;">
                        <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">Mobile Money Number</label>
                        <input type="text" name="mobile_number" value="{{ old('mobile_number') }}"
                               style="width:100%;padding:10px;border:1px solid #e2e8f0;border-radius:6px;" placeholder="+256 700 000000">
                    </div>
                </div>

                <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:24px;margin-bottom:20px;">
                    <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">Order Notes (optional)</label>
                    <textarea name="notes" rows="3"
                              style="width:100%;padding:10px;border:1px solid #e2e8f0;border-radius:6px;"
                              placeholder="Special instructions for delivery...">{{ old('notes') }}</textarea>
                </div>

                <button type="submit" class="btn-primary" style="width:100%;padding:14px;font-size:16px;">
                    Place Order
                </button>
            </form>
        </div>

        <!-- Order Summary -->
        <div>
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:20px;position:sticky;top:80px;">
                <h3 style="margin-bottom:16px;">Order Summary</h3>
                @foreach($items as $item)
                    <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:8px;padding-bottom:8px;border-bottom:1px solid #f1f5f9;">
                        <span>{{ $item['product']->title }} × {{ $item['quantity'] }}</span>
                        <span>UGX {{ number_format($item['product']->price * $item['quantity']) }}</span>
                    </div>
                @endforeach
                <hr style="margin:12px 0;border:none;border-top:1px solid #e2e8f0;">
                <div style="display:flex;justify-content:space-between;font-weight:700;margin-bottom:6px;">
                    <span>Deposit Due Now:</span>
                    <span style="color:#2563eb;">UGX {{ number_format($totalDeposit) }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:13px;color:#64748b;">
                    <span>Total Payable:</span>
                    <span>UGX {{ number_format($totalFull) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media (max-width: 768px) {
    .checkout-grid { grid-template-columns: 1fr !important; }
    .checkout-form-row { grid-template-columns: 1fr !important; }
}
</style>
@endsection
