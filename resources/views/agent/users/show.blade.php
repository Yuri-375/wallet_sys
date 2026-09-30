@extends('layouts.app')
@section('heading', 'User: '.$user->name)

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="card"><div class="card-body">
        <div class="text-muted small">Wallet Balance</div>
        <div class="fs-4 fw-bold">{{ number_format($user->wallet->balance ?? 0) }} MMK</div>
        @if ($user->wallet?->is_frozen)
            <span class="badge bg-danger">Frozen</span>
        @endif
    </div></div></div>

    <div class="col-md-4"><div class="card"><div class="card-body">
        <div class="text-muted small">KYC</div>
        <div class="fs-4 fw-bold text-capitalize">{{ $user->kyc_status }}</div>
        <a href="{{ route('agent.kyc.show', $user) }}" class="btn btn-sm btn-primary mt-2">Review KYC</a>
    </div></div></div>

    <div class="col-md-4"><div class="card"><div class="card-body">
        <div class="text-muted small">Quick Action</div>
        <a href="{{ route('agent.topups.create', ['user_id' => $user->id]) }}"
           class="btn btn-success mt-2">Cash Top-Up</a>
    </div></div></div>
</div>

<div class="card">
    <div class="card-header fw-bold">Recent Top-Ups</div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead><tr><th>Date</th><th class="text-end">Amount</th><th>Reference</th><th>Status</th></tr></thead>
            <tbody>
            @forelse ($user->topUps as $t)
                <tr>
                    <td>{{ $t->created_at->format('Y-m-d H:i') }}</td>
                    <td class="text-end">{{ number_format($t->amount) }}</td>
                    <td><code>{{ $t->reference }}</code></td>
                    <td class="text-capitalize">{{ $t->status }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted py-3">No top-ups.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection