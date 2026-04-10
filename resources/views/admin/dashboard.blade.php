@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value">{{ number_format($stats['total_products']) }}</div>
        <div class="stat-label">📦 Total Products</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ number_format($stats['total_customers']) }}</div>
        <div class="stat-label">👥 Customers</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ number_format($stats['total_orders']) }}</div>
        <div class="stat-label">🛒 Total Orders</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="font-size:20px;">UGX {{ number_format($stats['total_revenue']) }}</div>
        <div class="stat-label">💰 Total Revenue Collected</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="font-size:20px;color:#dc3545;">UGX {{ number_format($stats['outstanding_credit']) }}</div>
        <div class="stat-label">💳 Outstanding Credit</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color:#dc3545;">{{ $stats['overdue_payments'] }}</div>
        <div class="stat-label">⚠️ Overdue Payments</div>
    </div>
</div>

<!-- Quick Links -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;margin-bottom:24px;">
    <a href="{{ route('admin.products.create') }}" class="btn-primary" style="text-align:center;padding:12px;">+ Add Product</a>
    <a href="{{ route('admin.orders.index') }}" class="btn-secondary" style="text-align:center;padding:12px;">View All Orders</a>
    <a href="{{ route('admin.payments.index', ['filter'=>'overdue']) }}" style="background:#dc3545;color:#fff;text-align:center;padding:12px;border-radius:6px;text-decoration:none;">Overdue Payments</a>
    <a href="{{ route('admin.reports') }}" class="btn-secondary" style="text-align:center;padding:12px;">Reports</a>
</div>

<!-- Recent Orders -->
<div class="admin-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <h2 style="font-size:16px;font-weight:600;">Recent Orders</h2>
        <a href="{{ route('admin.orders.index') }}" style="color:#2563eb;font-size:14px;">View All</a>
    </div>
    @if($stats['recent_orders']->isEmpty())
        <p style="color:#64748b;text-align:center;padding:20px 0;">No orders yet.</p>
    @else
        <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stats['recent_orders'] as $order)
                    <tr>
                        <td><strong>{{ $order->order_number }}</strong></td>
                        <td>{{ $order->user->name }}</td>
                        <td>UGX {{ number_format($order->total_deposit) }}</td>
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
        </div>
    @endif
</div>

@endsection
