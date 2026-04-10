@extends('layouts.admin')
@section('title', 'Reports')

@section('content')

<!-- Summary Stats -->
<div class="stats-grid" style="margin-bottom:24px;">
    <div class="stat-card">
        <div class="stat-value" style="font-size:20px;">UGX {{ number_format($totalRevenue) }}</div>
        <div class="stat-label">💰 Total Revenue Collected</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="font-size:20px;color:#dc3545;">UGX {{ number_format($outstandingCredit) }}</div>
        <div class="stat-label">💳 Outstanding Credit</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="font-size:20px;">UGX {{ number_format($totalCreditPaid) }}</div>
        <div class="stat-label">✅ Total Credit Repaid</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color:#dc3545;">{{ $overdueCount }}</div>
        <div class="stat-label">⚠️ Overdue Payments</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;" class="reports-grid">

    <!-- Monthly Revenue -->
    <div class="admin-card">
        <h3 style="font-size:15px;font-weight:600;margin-bottom:16px;">Monthly Revenue</h3>
        @if($monthlyRevenue->isEmpty())
            <p style="color:#64748b;">No data yet.</p>
        @else
            <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr><th>Month</th><th>Orders</th><th>Revenue (UGX)</th></tr>
                </thead>
                <tbody>
                    @foreach($monthlyRevenue as $row)
                        @php
                            $monthName = \Carbon\Carbon::createFromDate($row->year, $row->month, 1)->format('M Y');
                        @endphp
                        <tr>
                            <td>{{ $monthName }}</td>
                            <td>{{ $row->orders }}</td>
                            <td>{{ number_format($row->revenue) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        @endif
    </div>

    <!-- Sales by Category -->
    <div class="admin-card">
        <h3 style="font-size:15px;font-weight:600;margin-bottom:16px;">Sales by Category</h3>
        @if($salesByCategory->isEmpty())
            <p style="color:#64748b;">No data yet.</p>
        @else
            <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr><th>Category</th><th>Units Sold</th></tr>
                </thead>
                <tbody>
                    @foreach($salesByCategory as $row)
                        <tr>
                            <td>{{ $row->category }}</td>
                            <td><strong>{{ number_format($row->total_sold) }}</strong></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        @endif
    </div>
</div>

<!-- Top Products -->
<div class="admin-card" style="margin-top:20px;">
    <h3 style="font-size:15px;font-weight:600;margin-bottom:16px;">Top 10 Products by Sales</h3>
    @if($topProducts->isEmpty())
        <p style="color:#64748b;">No sales data yet.</p>
    @else
        <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr><th>Rank</th><th>Product</th><th>Category</th><th>Units Sold</th></tr>
            </thead>
            <tbody>
                @foreach($topProducts as $i => $product)
                    <tr>
                        <td><strong>#{{ $i + 1 }}</strong></td>
                        <td>{{ $product->title }}</td>
                        <td><span class="badge badge-info">{{ $product->category }}</span></td>
                        <td><strong>{{ number_format($product->total_sold) }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    @endif
</div>

<style>
@media (max-width: 768px) {
    .reports-grid { grid-template-columns: 1fr !important; }
}
</style>

@endsection
