@extends('layouts.app')
@section('heading', 'Wallet — '.$wallet->user?->name)

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="card"><div class="card-body">
        <div class="text-muted small">Balance</div>
        <div class="fs-4 fw-bold">{{ number_format($wallet->balance) }} {{ $wallet->currency }}</div>
    </div></div></div>
    <div class="col-md-4"><div class="card"><div class="card-body">
        <div class="text-muted small">Ledger Net</div>
        <div class="fs-4 fw-bold">{{ number_format($ledgerNet) }}</div>
    </div></div></div>
    <div class="col-md-4"><div class="card"><div class="card-body">
        <div class="text-muted small">Drift</div>
        <div class="fs-4 fw-bold text-{{ $drift === 0 ? 'success' : 'danger' }}">
            {{ number_format($drift) }}
        </div>
        @if ($drift !== 0)
            <div class="small text-danger">⚠️ Investigate immediately!</div>
        @endif
    </div></div></div>
</div>

<div class="card mb-4">
    <div class="card-body">
        @if ($wallet->is_frozen)
            <div class="alert alert-danger mb-2">
                Frozen. Reason: {{ $wallet->freeze_reason }} ({{ $wallet->frozen_at?->diffForHumans() }})
            </div>
            <form method="POST" action="{{ route('admin.wallets.unfreeze', $wallet) }}">
                @csrf <button class="btn btn-success">Unfreeze</button>
            </form>
        @else
            <form method="POST" action="{{ route('admin.wallets.freeze', $wallet) }}" class="d-flex gap-2">
                @csrf
                <input name="reason" class="form-control" placeholder="Reason for freeze" required>
                <button class="btn btn-danger">Freeze</button>
            </form>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-header fw-bold">Recent Transactions</div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead><tr>
                <th>Date</th><th>Type</th><th>Direction</th>
                <th class="text-end">Amount</th><th class="text-end">Balance After</th>
                <th>Reference</th>
            </tr></thead>
            <tbody>
            @forelse ($transactions as $t)
                <tr>
                    <td>{{ $t->created_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $t->type }}</td>
                    <td class="{{ $t->direction === 'credit' ? 'text-success' : 'text-danger' }}">
                        {{ $t->direction }}
                    </td>
                    <td class="text-end">{{ number_format($t->amount) }}</td>
                    <td class="text-end">{{ number_format($t->balance_after) }}</td>
                    <td><code>{{ $t->reference }}</code></td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-3">No transactions.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@include('partials.pagination', ['paginator' => $transactions])
@endsection