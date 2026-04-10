@extends('layouts.admin')
@section('title', 'Customers')

@section('content')

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
    <h2 style="font-size:16px;font-weight:600;">All Customers ({{ $users->total() }})</h2>
</div>

<form method="GET" action="{{ route('admin.users.index') }}" class="search-filter-bar">
    <input type="text" name="search" placeholder="Search name or email..." value="{{ request('search') }}">
    <select name="status">
        <option value="">All Status</option>
        <option value="active"    {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
        <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
    </select>
    <button type="submit" class="btn-sm btn-primary-sm" style="padding:8px 14px;">Filter</button>
    <a href="{{ route('admin.users.index') }}" class="btn-sm" style="padding:8px 14px;background:#f1f5f9;color:#374151;border-radius:4px;text-decoration:none;">Clear</a>
</form>

<div class="admin-card" style="overflow-x:auto;">
    @if($users->isEmpty())
        <p style="text-align:center;color:#64748b;padding:40px 0;">No customers found.</p>
    @else
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Orders</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr>
                        <td><strong>{{ $user->name }}</strong></td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->phone ?? '—' }}</td>
                        <td>{{ $user->orders_count }}</td>
                        <td>
                            <span class="badge {{ $user->status === 'active' ? 'badge-success' : 'badge-danger' }}">
                                {{ ucfirst($user->status) }}
                            </span>
                        </td>
                        <td>{{ $user->created_at->format('d M Y') }}</td>
                        <td style="white-space:nowrap;">
                            <a href="{{ route('admin.users.show', $user) }}" class="btn-sm btn-primary-sm">View</a>
                            <a href="{{ route('admin.users.edit', $user) }}" class="btn-sm btn-warning-sm">Edit</a>
                            <form method="POST" action="{{ route('admin.users.status', $user) }}" style="display:inline;">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn-sm {{ $user->status === 'active' ? 'btn-danger-sm' : '' }}"
                                        style="{{ $user->status !== 'active' ? 'background:#28a745;color:#fff;' : '' }}">
                                    {{ $user->status === 'active' ? 'Suspend' : 'Activate' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div style="margin-top:16px;">{{ $users->links() }}</div>
    @endif
</div>

@endsection
