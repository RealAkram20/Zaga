@extends('layouts.admin')
@section('title', 'Order ' . $order->order_number)

@section('content')
<div style="max-width:900px;">
    <a href="{{ route('admin.orders.index') }}" style="color:#2563eb;font-size:14px;">&larr; Back to Orders</a>

    <div style="display:flex;justify-content:space-between;align-items:center;margin:16px 0;flex-wrap:wrap;gap:12px;">
        <h2 style="font-size:18px;">{{ $order->order_number }}</h2>
        <form method="POST" action="{{ route('admin.orders.status', $order) }}" style="display:flex;gap:8px;align-items:center;">
            @csrf @method('PATCH')
            <select name="status" style="padding:8px;border:1px solid #e2e8f0;border-radius:6px;font-size:14px;">
                @foreach(['pending','confirmed','completed','cancelled'] as $s)
                    <option value="{{ $s }}" {{ $order->status == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-sm btn-primary-sm" style="padding:8px 14px;">Update Status</button>
        </form>
    </div>

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;" class="order-admin-grid">
        <div>
            <!-- Items -->
            <div class="admin-card">
                <h3 style="font-size:15px;margin-bottom:16px;">Items Ordered</h3>
                <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr><th>Product</th><th>Type</th><th>Qty</th><th>Price</th><th>Total</th></tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td>{{ $item->product_title }}</td>
                                <td>
                                    <span class="badge {{ $item->payment_type === 'credit' ? 'badge-warning' : 'badge-success' }}">
                                        {{ $item->payment_type === 'credit' ? 'Credit ' . $item->credit_months . 'm' : 'Full' }}
                                    </span>
                                </td>
                                <td>{{ $item->quantity }}</td>
                                <td>UGX {{ number_format($item->unit_price) }}</td>
                                <td>UGX {{ number_format($item->unit_price * $item->quantity) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            </div>

            <!-- Payment Schedule -->
            @if($order->paymentSchedules->count() > 0)
                <div class="admin-card" style="margin-top:16px;">
                    <h3 style="font-size:15px;margin-bottom:16px;">Payment Schedule</h3>
                    <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr><th>#</th><th>Due Date</th><th>Amount</th><th>Principal</th><th>Interest</th><th>Balance</th><th>Status</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            @foreach($order->paymentSchedules as $i => $schedule)
                                <tr style="{{ $schedule->isOverdue() ? 'background:#fff5f5;' : '' }}">
                                    <td>{{ $i + 1 }}</td>
                                    <td>
                                        {{ $schedule->due_date->format('d M Y') }}
                                        @if($schedule->isOverdue())<span style="color:#dc3545;font-size:11px;"> OVERDUE</span>@endif
                                    </td>
                                    <td>UGX {{ number_format($schedule->amount) }}</td>
                                    <td>UGX {{ number_format($schedule->principal) }}</td>
                                    <td>UGX {{ number_format($schedule->interest) }}</td>
                                    <td>UGX {{ number_format($schedule->remaining_balance) }}</td>
                                    <td>
                                        <span class="badge {{ $schedule->paid ? 'badge-success' : 'badge-danger' }}">
                                            {{ $schedule->paid ? 'Paid' : 'Unpaid' }}
                                        </span>
                                        @if($schedule->paid)
                                            <br><small style="color:#64748b;">{{ $schedule->paid_date->format('d M Y') }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if(!$schedule->paid)
                                            <form method="POST" action="{{ route('admin.payments.markPaid', $schedule) }}">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="btn-sm btn-primary-sm">Mark Paid</button>
                                            </form>
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
            <div class="admin-card">
                <h3 style="font-size:15px;margin-bottom:12px;">Customer</h3>
                <p><strong>{{ $order->user->name }}</strong></p>
                <p style="color:#64748b;font-size:14px;">{{ $order->user->email }}</p>
                <p style="color:#64748b;font-size:14px;">{{ $order->user->phone ?? '' }}</p>
                <a href="{{ route('admin.users.show', $order->user) }}" style="color:#2563eb;font-size:13px;">View Profile</a>
            </div>

            <div class="admin-card" style="margin-top:16px;">
                <h3 style="font-size:15px;margin-bottom:12px;">Order Summary</h3>
                <div style="font-size:14px;">
                    <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                        <span>Deposit:</span><strong>UGX {{ number_format($order->total_deposit) }}</strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                        <span>Total:</span><strong>UGX {{ number_format($order->total_full) }}</strong>
                    </div>
                    @php $outstanding = $order->paymentSchedules->where('paid', false)->sum('amount'); @endphp
                    @if($outstanding > 0)
                        <div style="display:flex;justify-content:space-between;color:#dc3545;font-weight:700;">
                            <span>Outstanding:</span><span>UGX {{ number_format($outstanding) }}</span>
                        </div>
                    @endif
                    <div style="margin-top:8px;padding-top:8px;border-top:1px solid #f1f5f9;">
                        <span class="badge {{ ['pending'=>'badge-warning','confirmed'=>'badge-info','completed'=>'badge-success','cancelled'=>'badge-danger'][$order->status] }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="admin-card" style="margin-top:16px;">
                <h3 style="font-size:15px;margin-bottom:12px;">Delivery Address</h3>
                <div style="font-size:14px;color:#374151;">
                    <p>{{ $order->shipping_address['name'] }}</p>
                    <p>{{ $order->shipping_address['phone'] }}</p>
                    <p>{{ $order->shipping_address['address'] }}</p>
                    <p>{{ $order->shipping_address['city'] }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media (max-width: 768px) {
    .order-admin-grid { grid-template-columns: 1fr !important; }
}
</style>
@endsection
