@extends('layouts.app')
@section('heading', 'Cash Top-Ups')

@section('content')
<div class="mb-3">
    <a href="{{ route('agent.topups.create') }}" class="btn btn-primary">New Top-Up</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead><tr>
                <th>Date</th><th>User</th><th class="text-end">Amount</th>
                <th>Reference</th><th>Status</th>
            </tr></thead>
            <tbody>
            @forelse ($topUps as $t)
                <tr>
                    <td>{{ $t->created_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $t->user?->name }}</td>
                    <td class="text-end">{{ number_format($t->amount) }}</td>
                    <td><code>{{ $t->reference }}</code></td>
                    <td class="text-capitalize">{{ $t->status }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-3">No top-ups.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@include('partials.pagination', ['paginator' => $topUps])
@endsection