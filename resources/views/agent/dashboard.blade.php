@extends('layouts.app')
@section('heading', 'Agent Dashboard')

@section('content')
<div class="row g-3">
    <div class="col-md-4"><div class="card stat-card"><div class="card-body">
        <div class="text-muted small">Pending KYC</div>
        <div class="fs-3 fw-bold">{{ $pendingKyc }}</div>
        <a href="{{ route('agent.kyc.pending') }}" class="btn btn-sm btn-primary mt-2">Review</a>
    </div></div></div>

    <div class="col-md-4"><div class="card stat-card"><div class="card-body">
        <div class="text-muted small">Assigned Users</div>
        <div class="fs-3 fw-bold">{{ $assigned }}</div>
        <a href="{{ route('agent.users.index') }}" class="btn btn-sm btn-primary mt-2">View</a>
    </div></div></div>

    <div class="col-md-4"><div class="card stat-card"><div class="card-body">
        <div class="text-muted small">Top-Ups Today</div>
        <div class="fs-3 fw-bold">{{ $todayTopUps }}</div>
        <a href="{{ route('agent.topups.create') }}" class="btn btn-sm btn-primary mt-2">New</a>
    </div></div></div>
</div>
@endsection