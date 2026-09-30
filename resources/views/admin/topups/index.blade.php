@extends('layouts.app')
@section('heading', 'All Top-Ups')

@section('content')
<div class="card">
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead><tr>
                <th>Date</th><th>User</th><th>Method</th>
                <th class="text-end">Amount</th><th>Processed By</th><th>Status</th>
            </tr></thead>
            <tbody>
            @forelse ($topUps as $t)
                <tr>
                    <td>{{ $t->created_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $t->user?->name }}</td>
                    <td>{{ $t->method }}</td>
                    <td class="text-end">{{ number_format($t->amount) }}</td>
                    <td>{{ $t->processor?->name }}</td>
                    <td class="text-capitalize">{{ $t->status }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-3">No top-ups.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@include('partials.pagination', ['paginator' => $topUps])
@endsection