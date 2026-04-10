@extends('layouts.admin')
@section('title', 'Payment Schedules')

@section('content')

<div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;">
    <a href="{{ route('admin.payments.index') }}"
       class="btn-sm {{ !request('filter') ? 'btn-primary-sm' : '' }}"
       style="{{ !request('filter') ? '' : 'background:#f1f5f9;color:#374151;' }}padding:8px 14px;">All</a>
    <a href="{{ route('admin.payments.index', ['filter'=>'overdue']) }}"
       class="btn-sm {{ request('filter') == 'overdue' ? 'btn-danger-sm' : '' }}"
       style="{{ request('filter') != 'overdue' ? 'background:#fee2e2;color:#991b1b;' : '' }}padding:8px 14px;">Overdue</a>
    <a href="{{ route('admin.payments.index', ['filter'=>'upcoming']) }}"
       class="btn-sm {{ request('filter') == 'upcoming' ? 'btn-warning-sm' : '' }}"
       style="{{ request('filter') != 'upcoming' ? 'background:#fef9c3;color:#854d0e;' : '' }}padding:8px 14px;">Upcoming</a>
    <a href="{{ route('admin.payments.index', ['filter'=>'paid']) }}"
       class="btn-sm {{ request('filter') == 'paid' ? 'btn-primary-sm' : '' }}"
       style="{{ request('filter') != 'paid' ? 'background:#dcfce7;color:#166534;' : '' }}padding:8px 14px;">Paid</a>
</div>

<div class="admin-card" style="overflow-x:auto;">
    @if($schedules->isEmpty())
        <p style="text-align:center;color:#64748b;padding:40px 0;">No payments found.</p>
    @else
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Due Date</th>
                    <th>Amount (UGX)</th>
                    <th>Interest (UGX)</th>
                    <th>Balance (UGX)</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($schedules as $schedule)
                    <tr style="{{ $schedule->isOverdue() ? 'background:#fff5f5;' : '' }}">
                        <td>
                            <a href="{{ route('admin.orders.show', $schedule->order) }}" style="color:#2563eb;">
                                {{ $schedule->order->order_number }}
                            </a>
                        </td>
                        <td>{{ $schedule->order->user->name ?? '—' }}</td>
                        <td>
                            {{ $schedule->due_date->format('d M Y') }}
                            @if($schedule->isOverdue())
                                <span style="color:#dc3545;font-size:11px;display:block;">OVERDUE</span>
                            @endif
                        </td>
                        <td>{{ number_format($schedule->amount) }}</td>
                        <td>{{ number_format($schedule->interest) }}</td>
                        <td>{{ number_format($schedule->remaining_balance) }}</td>
                        <td>
                            @if($schedule->paid)
                                <span class="badge badge-success">Paid</span>
                                <small style="display:block;color:#64748b;font-size:11px;">{{ $schedule->paid_date->format('d M Y') }}</small>
                            @elseif($schedule->isOverdue())
                                <span class="badge badge-danger">Overdue</span>
                            @else
                                <span class="badge badge-warning">Pending</span>
                            @endif
                        </td>
                        <td>
                            @if(!$schedule->paid)
                                <form method="POST" action="{{ route('admin.payments.markPaid', $schedule) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn-sm btn-primary-sm">Mark Paid</button>
                                </form>
                            @else
                                <span style="color:#64748b;font-size:13px;">UGX {{ number_format($schedule->paid_amount) }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div style="margin-top:16px;">{{ $schedules->links() }}</div>
    @endif
</div>

@endsection
