@extends('layouts.app')
@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card stat-card"><div class="card-body">
            <div class="text-muted small">Wallet Balance</div>
            <div class="fs-3 fw-bold">{{ number_format($wallet->balance ?? 0) }} MMK</div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card"><div class="card-body">
            <div class="text-muted small">KYC Status</div>
            <div class="fs-3 fw-bold text-capitalize">{{ $user->kyc_status }}</div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card"><div class="card-body">
            <div class="text-muted small">Account Status</div>
            <div class="fs-3 fw-bold text-capitalize">{{ $user->status }}</div>
        </div></div>
    </div>
</div>

<div class="card">
    <div class="card-header fw-bold">Recent Activity</div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead>
            <tr>
                <th>Date</th><th>Type</th><th>Direction</th>
                <th class="text-end">Amount</th><th class="text-end">Balance After</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($recentTransactions as $t)
                <tr>
                    <td>{{ $t->created_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $t->type }}</td>
                    <td class="{{ $t->direction === 'credit' ? 'text-success' : 'text-danger' }}">
                        {{ ucfirst($t->direction) }}
                    </td>
                    <td class="text-end">{{ number_format($t->amount) }}</td>
                    <td class="text-end">{{ number_format($t->balance_after) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-3">No activity yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection