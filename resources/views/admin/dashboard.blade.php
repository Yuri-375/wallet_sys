@extends('layouts.app')
@section('heading', 'Admin Dashboard')

@section('content')
<div class="row g-3 mb-4">
    @foreach ([
        'Users' => $stats['users'],
        'Agents' => $stats['agents'],
        'Wallets' => $stats['wallets'],
        'Total Balance' => number_format($stats['total_balance']).' MMK',
        'Frozen Wallets' => $stats['frozen_wallets'],
        'Pending KYC' => $stats['pending_kyc'],
        'Open Fraud' => $stats['open_fraud'],
        'Transfers Today' => $stats['transfers_today'],
        'Top-Ups Today' => $stats['topups_today'],
        'Bills Today' => $stats['billpay_today'],
    ] as $label => $value)
        <div class="col-md-3"><div class="card stat-card"><div class="card-body">
            <div class="text-muted small">{{ $label }}</div>
            <div class="fs-4 fw-bold">{{ $value }}</div>
        </div></div></div>
    @endforeach
</div>

<div class="card">
    <div class="card-header fw-bold">Recent Transfers</div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead><tr>
                <th>Date</th><th>From</th><th>To</th>
                <th class="text-end">Amount</th><th>Status</th>
            </tr></thead>
            <tbody>
            @forelse ($recentTransfers as $t)
                <tr>
                    <td>{{ $t->created_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $t->sender?->name }}</td>
                    <td>{{ $t->receiver?->name }}</td>
                    <td class="text-end">{{ number_format($t->amount) }}</td>
                    <td class="text-capitalize">{{ $t->status }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-3">No transfers.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection