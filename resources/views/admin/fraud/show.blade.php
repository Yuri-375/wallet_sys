@extends('layouts.app')
@section('heading', 'Fraud Report #'.$report->id)

@section('content')
<div class="card mb-3"><div class="card-body">
    <div><strong>User:</strong> {{ $report->user?->name }} ({{ $report->user?->email }})</div>
    <div><strong>Rule:</strong> {{ $report->fraudRule?->name }} ({{ $report->fraudRule?->code }})</div>
    <div><strong>Severity:</strong> {{ $report->severity }}</div>
    <div><strong>Status:</strong> {{ $report->status }}</div>
    <div><strong>Description:</strong> {{ $report->description }}</div>
    <div><strong>Assigned to:</strong> {{ $report->assignedUser?->name ?? '—' }}</div>
    <div class="mt-2"><strong>Metadata:</strong>
        <pre class="small">{{ json_encode($report->metadata, JSON_PRETTY_PRINT) }}</pre>
    </div>
</div></div>

@if ($report->status === 'open')
    <form method="POST" action="{{ route('admin.fraud.investigate', $report) }}" class="mb-3">
        @csrf <button class="btn btn-warning">Start Investigation</button>
    </form>
@endif

@if (in_array($report->status, ['open','investigating']))
    <form method="POST" action="{{ route('admin.fraud.resolve', $report) }}" class="card p-4" style="max-width:640px;">
        @csrf
        <div class="mb-3">
            <label>Resolution Notes</label>
            <textarea name="resolution_notes" class="form-control" required></textarea>
        </div>
        <button class="btn btn-success">Resolve</button>
    </form>
@endif
@endsection