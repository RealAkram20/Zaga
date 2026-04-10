@extends('layouts.app')
@section('title', 'Order ' . $order->order_number)

@section('content')
<div class="container" style="padding:32px 16px;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
        <div>
            <h2 style="margin-bottom:4px;">Order {{ $order->order_number }}</h2>
            <p style="color:#64748b;font-size:14px;">Placed {{ $order->created_at->format('d M Y, h:i A') }}</p>
        </div>
        <span style="display:inline-block;padding:6px 16px;border-radius:12px;font-weight:500;
            background:{{ ['pending'=>'#fef9c3','confirmed'=>'#dbeafe','completed'=>'#dcfce7','cancelled'=>'#fee2e2'][$order->status] }};
            color:{{ ['pending'=>'#854d0e','confirmed'=>'#1e40af','completed'=>'#166534','cancelled'=>'#991b1b'][$order->status] }};">
            {{ ucfirst($order->status) }}
        </span>
    </div>

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;" class="order-detail-grid">
        <div>
            <!-- Items -->
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:20px;margin-bottom:20px;">
                <h3 style="margin-bottom:16px;">Items Ordered</h3>
                @foreach($order->items as $item)
                    <div style="display:flex;gap:12px;padding:12px 0;border-bottom:1px solid #f1f5f9;">
                        <img src="{{ asset($item->product->image ?? 'images/logo.png') }}" alt="{{ $item->product_title }}"
                             style="width:60px;height:60px;object-fit:contain;border:1px solid #e2e8f0;border-radius:4px;">
                        <div style="flex:1;">
                            <p style="font-weight:600;margin-bottom:4px;">{{ $item->product_title }}</p>
                            <p style="font-size:13px;color:#64748b;">
                                {{ $item->payment_type === 'credit' ? 'Credit — ' . $item->credit_months . ' months @ ' . $item->interest_rate . '% APR' : 'Full Payment' }}
                                | Qty: {{ $item->quantity }}
                            </p>
                        </div>
                        <div style="font-weight:700;color:#2563eb;">
                            UGX {{ number_format($item->unit_price * $item->quantity) }}
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Payment Schedule -->
            @if($order->paymentSchedules->count() > 0)
                <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:20px;">
                    <h3 style="margin-bottom:16px;">Payment Schedule</h3>
                    <div class="table-responsive">
                    <table style="width:100%;border-collapse:collapse;font-size:14px;">
                        <thead>
                            <tr style="background:#f8fafc;">
                                <th style="padding:8px 12px;text-align:left;border-bottom:1px solid #e2e8f0;">#</th>
                                <th style="padding:8px 12px;text-align:left;border-bottom:1px solid #e2e8f0;">Due Date</th>
                                <th style="padding:8px 12px;text-align:right;border-bottom:1px solid #e2e8f0;">Amount</th>
                                <th style="padding:8px 12px;text-align:right;border-bottom:1px solid #e2e8f0;">Balance</th>
                                <th style="padding:8px 12px;text-align:center;border-bottom:1px solid #e2e8f0;">Status</th>
                                <th style="padding:8px 12px;text-align:center;border-bottom:1px solid #e2e8f0;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->paymentSchedules as $i => $schedule)
                                <tr style="{{ $schedule->isOverdue() ? 'background:#fff5f5;' : '' }}">
                                    <td style="padding:8px 12px;border-bottom:1px solid #f1f5f9;">{{ $i + 1 }}</td>
                                    <td style="padding:8px 12px;border-bottom:1px solid #f1f5f9;">
                                        {{ $schedule->due_date->format('d M Y') }}
                                        @if($schedule->isOverdue())
                                            <span style="color:#dc3545;font-size:11px;"> OVERDUE</span>
                                        @endif
                                    </td>
                                    <td style="padding:8px 12px;text-align:right;border-bottom:1px solid #f1f5f9;">
                                        UGX {{ number_format($schedule->amount) }}
                                    </td>
                                    <td style="padding:8px 12px;text-align:right;border-bottom:1px solid #f1f5f9;color:#64748b;">
                                        UGX {{ number_format($schedule->remaining_balance) }}
                                    </td>
                                    <td style="padding:8px 12px;text-align:center;border-bottom:1px solid #f1f5f9;">
                                        @if($schedule->paid)
                                            <span style="background:#dcfce7;color:#166534;padding:2px 8px;border-radius:12px;font-size:12px;">Paid</span>
                                        @else
                                            <span style="background:#fee2e2;color:#991b1b;padding:2px 8px;border-radius:12px;font-size:12px;">Unpaid</span>
                                        @endif
                                    </td>
                                    <td style="padding:8px 12px;text-align:center;border-bottom:1px solid #f1f5f9;">
                                        @if(!$schedule->paid)
                                            <a href="{{ route('payments.show', [$order, $schedule]) }}"
                                               class="btn-primary" style="padding:8px 14px;font-size:13px;min-height:40px;display:inline-flex;align-items:center;">Pay Now</a>
                                        @else
                                            <span style="color:#64748b;font-size:12px;">{{ $schedule->paid_date->format('d M Y') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div>
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:20px;margin-bottom:16px;">
                <h3 style="margin-bottom:16px;">Order Summary</h3>
                <div style="font-size:14px;">
                    <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                        <span>Deposit Paid</span>
                        <strong>UGX {{ number_format($order->total_deposit) }}</strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                        <span>Total Payable</span>
                        <strong>UGX {{ number_format($order->total_full) }}</strong>
                    </div>
                    @php $outstanding = $order->paymentSchedules->where('paid', false)->sum('amount'); @endphp
                    @if($outstanding > 0)
                        <div style="display:flex;justify-content:space-between;color:#dc3545;font-weight:700;">
                            <span>Outstanding</span>
                            <span>UGX {{ number_format($outstanding) }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:20px;">
                <h3 style="margin-bottom:12px;">Delivery Address</h3>
                <div style="font-size:14px;color:#374151;">
                    <p>{{ $order->shipping_address['name'] }}</p>
                    <p>{{ $order->shipping_address['phone'] }}</p>
                    <p>{{ $order->shipping_address['address'] }}</p>
                    <p>{{ $order->shipping_address['city'] }}</p>
                </div>
            </div>
        </div>
    </div>
    <div style="margin-top:20px;">
        <a href="{{ route('orders.index') }}" style="color:#2563eb;">&larr; Back to Orders</a>
    </div>
</div>
<style>
@media (max-width: 768px) {
    .order-detail-grid { grid-template-columns: 1fr !important; }
}
</style>
@endsection
