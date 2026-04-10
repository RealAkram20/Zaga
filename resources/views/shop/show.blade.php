@extends('layouts.app')
@section('title', $product->title)

@section('content')
<div class="container" style="padding:32px 16px;">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:32px;" class="product-detail-grid">

        <!-- Product Image -->
        <div>
            <img src="{{ asset($product->image ?? 'images/logo.png') }}" alt="{{ $product->title }}"
                 style="width:100%;max-height:400px;object-fit:contain;border:1px solid #e2e8f0;border-radius:8px;padding:16px;">
        </div>

        <!-- Product Info -->
        <div>
            <p style="color:#64748b;font-size:14px;margin-bottom:8px;">{{ $product->category }}</p>
            <h1 style="font-size:26px;color:#1e293b;margin-bottom:12px;">{{ $product->title }}</h1>

            <div class="rating" style="margin-bottom:12px;">
                @for($i = 1; $i <= 5; $i++)
                    <span style="color:{{ $i <= $product->rating ? '#f59e0b' : '#d1d5db' }};font-size:18px;">★</span>
                @endfor
                <span style="color:#64748b;font-size:14px;">({{ $product->reviews }} reviews)</span>
            </div>

            <div class="price-section" style="margin-bottom:20px;">
                <span class="price" style="font-size:28px;">UGX {{ number_format($product->price) }}</span>
                @if($product->original_price)
                    <span class="original-price" style="margin-left:12px;">UGX {{ number_format($product->original_price) }}</span>
                @endif
                @if($product->discount)
                    <span class="discount-badge" style="position:static;margin-left:8px;">-{{ $product->discount }}%</span>
                @endif
            </div>

            <p style="color:#374151;line-height:1.6;margin-bottom:20px;">{{ $product->description }}</p>

            @if($product->features)
                <ul style="color:#374151;margin-bottom:20px;padding-left:20px;">
                    @foreach($product->features as $feature)
                        <li style="margin-bottom:4px;">{{ $feature }}</li>
                    @endforeach
                </ul>
            @endif

            <div style="display:flex;gap:12px;font-size:13px;color:#64748b;margin-bottom:24px;flex-wrap:wrap;">
                @if($product->sku)     <span>SKU: <strong>{{ $product->sku }}</strong></span> @endif
                @if($product->warranty)<span>Warranty: <strong>{{ $product->warranty }}</strong></span> @endif
                <span>Stock: <strong>{{ $product->stock }}</strong></span>
            </div>

            @auth
                <!-- Add to Cart: Full Payment -->
                <div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:16px;">
                    <form method="POST" action="{{ route('cart.add') }}">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="quantity" value="1">
                        <input type="hidden" name="payment_type" value="full">
                        <button type="submit" class="btn-primary">Pay in Full &mdash; UGX {{ number_format($product->price) }}</button>
                    </form>
                </div>

                @if($product->credit_available)
                <div style="border:1px solid #e2e8f0;border-radius:8px;padding:20px;background:#f8fafc;">
                    <h3 style="margin-bottom:16px;color:#1e293b;">💳 Buy on Credit</h3>
                    <form method="POST" action="{{ route('cart.add') }}" id="creditForm">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="quantity" value="1">
                        <input type="hidden" name="payment_type" value="credit">

                        <div class="credit-form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px;">
                            <div>
                                <label style="font-size:13px;font-weight:600;color:#374151;display:block;margin-bottom:6px;">Term</label>
                                <select name="credit_months" id="creditMonths" style="width:100%;padding:8px;border:1px solid #e2e8f0;border-radius:6px;" onchange="updateCreditCalc()">
                                    <option value="3">3 Months</option>
                                    <option value="6">6 Months</option>
                                </select>
                            </div>
                            <div>
                                <label style="font-size:13px;font-weight:600;color:#374151;display:block;margin-bottom:6px;">Interest Rate</label>
                                <select name="interest_rate" id="creditRate" style="width:100%;padding:8px;border:1px solid #e2e8f0;border-radius:6px;" onchange="updateCreditCalc()">
                                    <option value="0">0% APR</option>
                                    <option value="5">5% APR</option>
                                    <option value="9.99" selected>9.99% APR</option>
                                    <option value="14.99">14.99% APR</option>
                                </select>
                            </div>
                        </div>

                        <div id="creditSummary" style="background:#fff;border:1px solid #e2e8f0;border-radius:6px;padding:14px;margin-bottom:16px;font-size:14px;">
                            <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                                <span>Deposit (20%):</span>
                                <strong id="depositAmt">—</strong>
                            </div>
                            <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                                <span>Monthly Payment:</span>
                                <strong id="monthlyAmt">—</strong>
                            </div>
                            <div style="display:flex;justify-content:space-between;">
                                <span>Total Payable:</span>
                                <strong id="totalAmt">—</strong>
                            </div>
                        </div>

                        <button type="submit" class="btn-secondary" style="width:100%;">Add to Cart on Credit</button>
                    </form>
                </div>
                @endif
            @else
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:20px;text-align:center;">
                    <p style="margin-bottom:12px;color:#64748b;">Sign in to purchase or apply for credit</p>
                    <a href="{{ route('login') }}" class="btn-primary" style="margin-right:8px;">Sign In</a>
                    <a href="{{ route('register') }}" class="btn-secondary">Register</a>
                </div>
            @endauth
        </div>
    </div>
</div>

<style>
@media (max-width: 768px) {
    .product-detail-grid {
        grid-template-columns: 1fr !important;
        gap: 20px !important;
    }
}
@media (max-width: 480px) {
    .credit-form-row { grid-template-columns: 1fr !important; }
}
</style>

@push('scripts')
<script>
const PRODUCT_PRICE = {{ $product->price }};

function updateCreditCalc() {
    const months = parseInt(document.getElementById('creditMonths').value);
    const apr = parseFloat(document.getElementById('creditRate').value);
    const deposit = Math.round(PRODUCT_PRICE * 0.2);
    const financed = PRODUCT_PRICE - deposit;

    let monthly;
    if (apr === 0) {
        monthly = Math.round(financed / months);
    } else {
        const r = apr / 100 / 12;
        const factor = Math.pow(1 + r, months);
        monthly = Math.round(financed * (r * factor) / (factor - 1));
    }

    const fmt = n => 'UGX ' + n.toLocaleString();
    document.getElementById('depositAmt').textContent = fmt(deposit);
    document.getElementById('monthlyAmt').textContent = fmt(monthly);
    document.getElementById('totalAmt').textContent = fmt(deposit + monthly * months);
}

document.addEventListener('DOMContentLoaded', updateCreditCalc);
</script>
@endpush
@endsection
