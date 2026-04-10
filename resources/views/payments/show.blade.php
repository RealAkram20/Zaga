@extends('layouts.app')
@section('title', 'Make Payment')

@section('content')
<div class="container" style="padding:32px 16px;max-width:600px;">
    <h2 style="margin-bottom:8px;">Make Payment</h2>
    <p style="color:#64748b;margin-bottom:24px;">Order: {{ $order->order_number }}</p>

    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:24px;margin-bottom:20px;">
        <h3 style="margin-bottom:16px;color:#1e293b;">Installment Details</h3>
        <table style="width:100%;font-size:14px;">
            <tr>
                <td style="padding:6px 0;color:#64748b;">Due Date</td>
                <td style="text-align:right;font-weight:600;">{{ $schedule->due_date->format('d M Y') }}</td>
            </tr>
            <tr>
                <td style="padding:6px 0;color:#64748b;">Amount Due</td>
                <td style="text-align:right;font-weight:700;color:#2563eb;">UGX {{ number_format($schedule->amount) }}</td>
            </tr>
            <tr>
                <td style="padding:6px 0;color:#64748b;">Principal</td>
                <td style="text-align:right;">UGX {{ number_format($schedule->principal) }}</td>
            </tr>
            <tr>
                <td style="padding:6px 0;color:#64748b;">Interest</td>
                <td style="text-align:right;">UGX {{ number_format($schedule->interest) }}</td>
            </tr>
            <tr>
                <td style="padding:6px 0;color:#64748b;">Remaining Balance After</td>
                <td style="text-align:right;">UGX {{ number_format($schedule->remaining_balance) }}</td>
            </tr>
        </table>
    </div>

    @if($schedule->isOverdue())
        <div style="background:#fee2e2;border:1px solid #fecaca;border-radius:6px;padding:12px 16px;margin-bottom:20px;color:#991b1b;">
            ⚠️ This payment is overdue. Please pay as soon as possible.
        </div>
    @endif

    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:24px;">
        <h3 style="margin-bottom:16px;">Confirm Payment</h3>
        <p style="font-size:14px;color:#64748b;margin-bottom:20px;">
            Pay via Mobile Money (+256 700 706809) or Cash. Your account will be updated once confirmed.
        </p>

        <form method="POST" action="{{ route('payments.process', [$order, $schedule]) }}">
            @csrf
            <input type="hidden" name="amount" value="{{ $schedule->amount }}">

            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:16px;margin-bottom:20px;text-align:center;">
                <div style="font-size:28px;font-weight:700;color:#2563eb;">
                    UGX {{ number_format($schedule->amount) }}
                </div>
                <div style="font-size:14px;color:#64748b;margin-top:4px;">Amount to confirm</div>
            </div>

            <button type="submit" class="btn-primary" style="width:100%;padding:14px;font-size:16px;">
                Confirm Payment
            </button>
        </form>
    </div>

    <div style="margin-top:16px;">
        <a href="{{ route('orders.show', $order) }}" style="color:#2563eb;">&larr; Back to Order</a>
    </div>
</div>
@endsection
