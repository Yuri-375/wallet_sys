@extends('layouts.app')
@section('heading', 'My Wallet')

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <div class="text-muted">Balance</div>
        <div class="display-5 fw-bold">{{ number_format($wallet->balance) }} {{ $wallet->currency }}</div>
        <div class="mt-2">
            Status:
            <span class="badge bg-{{ $wallet->is_frozen ? 'danger' : 'success' }}">
                {{ $wallet->is_frozen ? 'Frozen' : 'Active' }}
            </span>
            @if ($wallet->is_frozen && $wallet->freeze_reason)
                <div class="text-danger mt-2">Reason: {{ $wallet->freeze_reason }}</div>
            @endif
        </div>
    </div>
</div>

<div class="d-flex gap-2 mb-4">
    <a href="{{ route('user.transfers.create') }}" class="btn btn-primary">Send Money</a>
    <a href="{{ route('user.bills.create') }}" class="btn btn-success">Pay Bill</a>
    <a href="{{ route('user.transactions.index') }}" class="btn btn-outline-secondary">All Transactions</a>
</div>
@endsection