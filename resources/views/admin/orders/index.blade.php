@extends('layouts.admin')
@section('title', 'Orders')

@section('content')

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
    <h2 style="font-size:16px;font-weight:600;">All Orders ({{ $orders->total() }})</h2>
</div>

<form method="GET" action="{{ route('admin.orders.index') }}" class="search-filter-bar">
    <input type="text" name="search" placeholder="Order # or customer name..." value="{{ request('search') }}">
    <select name="status">
        <option value="">All Status</option>
        @foreach(['pending','confirmed','completed','cancelled'] as $s)
            <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn-sm btn-primary-sm" style="padding:8px 14px;">Filter</button>
    <a href="{{ route('admin.orders.index') }}" class="btn-sm" style="padding:8px 14px;background:#f1f5f9;color:#374151;border-radius:4px;text-decoration:none;">Clear</a>
</form>

<div class="admin-card" style="overflow-x:auto;">
    @if($orders->isEmpty())
        <p style="text-align:center;color:#64748b;padding:40px 0;">No orders found.</p>
    @else
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Items</th>
                    <th>Deposit</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                    <tr>
                        <td><strong>{{ $order->order_number }}</strong></td>
                        <td>
                            <a href="{{ route('admin.users.show', $order->user) }}" style="color:#2563eb;">
                                {{ $order->user->name }}
                            </a>
                        </td>
                        <td>{{ $order->items_count ?? '—' }}</td>
                        <td>UGX {{ number_format($order->total_deposit) }}</td>
                        <td>UGX {{ number_format($order->total_full) }}</td>
                        <td><span class="badge badge-secondary">{{ str_replace('_',' ',ucfirst($order->payment_method)) }}</span></td>
                        <td>
                            <span class="badge {{ ['pending'=>'badge-warning','confirmed'=>'badge-info','completed'=>'badge-success','cancelled'=>'badge-danger'][$order->status] }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>
                        <td>{{ $order->created_at->format('d M Y') }}</td>
                        <td>
                            <a href="{{ route('admin.orders.show', $order) }}" class="btn-sm btn-primary-sm">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div style="margin-top:16px;">{{ $orders->links() }}</div>
    @endif
</div>

@endsection
