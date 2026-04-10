@extends('layouts.app')
@section('title', 'My Orders')

@section('content')
<div class="container" style="padding:32px 16px;">
    <h2 style="margin-bottom:24px;">My Orders</h2>

    @if($orders->isEmpty())
        <div style="text-align:center;padding:60px 0;">
            <p style="color:#64748b;font-size:18px;margin-bottom:16px;">You have no orders yet.</p>
            <a href="{{ route('shop.index') }}" class="btn-primary">Start Shopping</a>
        </div>
    @else
        @foreach($orders as $order)
            @php
                $outstanding = $order->paymentSchedules->where('paid', false)->sum('amount');
            @endphp
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:20px;margin-bottom:16px;">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;">
                    <div>
                        <h3 style="font-size:16px;margin-bottom:4px;">{{ $order->order_number }}</h3>
                        <p style="font-size:13px;color:#64748b;">Placed on {{ $order->created_at->format('d M Y') }}</p>
                    </div>
                    <div style="text-align:right;">
                        <span style="display:inline-block;padding:4px 12px;border-radius:12px;font-size:13px;font-weight:500;
                            background:{{ ['pending'=>'#fef9c3','confirmed'=>'#dbeafe','completed'=>'#dcfce7','cancelled'=>'#fee2e2'][$order->status] }};
                            color:{{ ['pending'=>'#854d0e','confirmed'=>'#1e40af','completed'=>'#166534','cancelled'=>'#991b1b'][$order->status] }};">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                </div>

                <div style="margin-top:16px;padding-top:16px;border-top:1px solid #f1f5f9;display:flex;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                    <div style="font-size:14px;">
                        <p>Items: <strong>{{ $order->items->count() }}</strong></p>
                        <p>Deposit Paid: <strong>UGX {{ number_format($order->total_deposit) }}</strong></p>
                        @if($outstanding > 0)
                            <p style="color:#dc3545;">Outstanding: <strong>UGX {{ number_format($outstanding) }}</strong></p>
                        @endif
                    </div>
                    <div style="display:flex;gap:8px;align-items:flex-end;">
                        <a href="{{ route('orders.show', $order) }}" class="btn-primary">View Details</a>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection
