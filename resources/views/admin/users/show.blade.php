@extends('layouts.admin')
@section('title', $user->name)

@section('content')
<div style="max-width:800px;">
    <a href="{{ route('admin.users.index') }}" style="color:#2563eb;font-size:14px;">&larr; Back to Customers</a>

    <div class="admin-card" style="margin-top:16px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;margin-bottom:24px;">
            <div>
                <h2 style="font-size:20px;margin-bottom:4px;">{{ $user->name }}</h2>
                <p style="color:#64748b;font-size:14px;">{{ $user->email }}</p>
            </div>
            <div style="display:flex;gap:8px;align-items:center;">
                <span class="badge {{ $user->status === 'active' ? 'badge-success' : 'badge-danger' }}" style="font-size:14px;">
                    {{ ucfirst($user->status) }}
                </span>
                <a href="{{ route('admin.users.edit', $user) }}" class="btn-sm btn-warning-sm">Edit</a>
                <form method="POST" action="{{ route('admin.users.status', $user) }}" style="display:inline;">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn-sm {{ $user->status === 'active' ? 'btn-danger-sm' : '' }}"
                            style="{{ $user->status !== 'active' ? 'background:#28a745;color:#fff;' : '' }}">
                        {{ $user->status === 'active' ? 'Suspend' : 'Activate' }}
                    </button>
                </form>
            </div>
        </div>

        <div class="admin-user-info-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px;">
            <div>
                <p style="font-size:13px;color:#64748b;margin-bottom:4px;">Phone</p>
                <p style="font-weight:600;">{{ $user->phone ?? 'Not provided' }}</p>
            </div>
            <div>
                <p style="font-size:13px;color:#64748b;margin-bottom:4px;">Address</p>
                <p style="font-weight:600;">{{ $user->address ?? 'Not provided' }}</p>
            </div>
            <div>
                <p style="font-size:13px;color:#64748b;margin-bottom:4px;">Member Since</p>
                <p style="font-weight:600;">{{ $user->created_at->format('d M Y') }}</p>
            </div>
            <div>
                <p style="font-size:13px;color:#64748b;margin-bottom:4px;">Total Orders</p>
                <p style="font-weight:600;">{{ $user->orders->count() }}</p>
            </div>
        </div>
    </div>

    <!-- Order History -->
    <div class="admin-card" style="margin-top:16px;">
        <h3 style="font-size:15px;font-weight:600;margin-bottom:16px;">Order History</h3>
        @if($user->orders->isEmpty())
            <p style="color:#64748b;">No orders placed yet.</p>
        @else
            <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Items</th>
                        <th>Deposit</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($user->orders as $order)
                        <tr>
                            <td><strong>{{ $order->order_number }}</strong></td>
                            <td>{{ $order->items->count() }}</td>
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
</div>
<style>
@media (max-width: 768px) {
    .admin-user-info-grid { grid-template-columns: 1fr !important; }
}
</style>
@endsection
