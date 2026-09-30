@extends('layouts.app')
@section('heading', 'Fraud Reports')

@section('content')
<form method="GET" class="row g-2 mb-3">
    <div class="col-auto">
        <select name="status" class="form-select" onchange="this.form.submit()">
            <option value="">All statuses</option>
            @foreach (['open','investigating','resolved','dismissed'] as $s)
                <option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto">
        <select name="severity" class="form-select" onchange="this.form.submit()">
            <option value="">Any severity</option>
            @foreach (['low','medium','high'] as $s)
                <option value="{{ $s }}" @selected(request('severity') === $s)>{{ $s }}</option>
            @endforeach
        </select>
    </div>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead><tr>
                <th>Date</th><th>User</th><th>Rule</th>
                <th>Severity</th><th>Status</th><th></th>
            </tr></thead>
            <tbody>
            @forelse ($reports as $r)
                <tr>
                    <td>{{ $r->created_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $r->user?->name }}</td>
                    <td>{{ $r->fraudRule?->name }}</td>
                    <td>
                        <span class="badge bg-{{ $r->severity === 'high' ? 'danger' : ($r->severity === 'medium' ? 'warning' : 'secondary') }}">
                            {{ $r->severity }}
                        </span>
                    </td>
                    <td class="text-capitalize">{{ $r->status }}</td>
                    <td><a href="{{ route('admin.fraud.show', $r) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-3">No reports.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@include('partials.pagination', ['paginator' => $reports])
@endsection