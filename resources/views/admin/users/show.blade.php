@extends('layouts.app')
@section('heading', 'User — '.$user->name)

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="card"><div class="card-body">
        <div class="text-muted small">Balance</div>
        <div class="fs-4 fw-bold">{{ number_format($user->wallet->balance ?? 0) }} MMK</div>
        @if ($user->wallet?->is_frozen)<span class="badge bg-danger">Frozen</span>@endif
    </div></div></div>
    <div class="col-md-4"><div class="card"><div class="card-body">
        <div class="text-muted small">KYC</div>
        <div class="fs-4 fw-bold text-capitalize">{{ $user->kyc_status }}</div>
    </div></div></div>
    <div class="col-md-4"><div class="card"><div class="card-body">
        <div class="text-muted small">Status</div>
        <div class="fs-4 fw-bold text-capitalize">{{ $user->status }}</div>
    </div></div></div>
</div>

<div class="d-flex gap-2 mb-4">
    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-outline-secondary">Edit</a>
    @if ($user->status === 'active')
        <form method="POST" action="{{ route('admin.users.suspend', $user) }}">
            @csrf <button class="btn btn-warning">Suspend</button>
        </form>
    @else
        <form method="POST" action="{{ route('admin.users.activate', $user) }}">
            @csrf <button class="btn btn-success">Activate</button>
        </form>
    @endif
    @if ($user->wallet)
        <a href="{{ route('admin.wallets.show', $user->wallet) }}" class="btn btn-outline-primary">
            Manage Wallet
        </a>
    @endif
</div>

<div class="card">
    <div class="card-header fw-bold">KYC Documents</div>
    <div class="card-body">
        @forelse ($user->kycDocuments as $d)
            <div class="mb-2">
                {{ $d->document_type }} — <span class="text-capitalize">{{ $d->status }}</span>
            </div>
        @empty
            <div class="text-muted">None.</div>
        @endforelse
    </div>
</div>
@endsection