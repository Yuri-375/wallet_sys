@extends('layouts.app')
@section('heading', 'Transactions')

@section('content')
<div class="card">
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead>
            <tr>
                <th>Date</th><th>Type</th><th>Direction</th>
                <th class="text-end">Amount</th><th class="text-end">Balance After</th>
                <th>Reference</th><th>Status</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($transactions as $t)
                <tr>
                    <td>{{ $t->created_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $t->type }}</td>
                    <td class="{{ $t->direction === 'credit' ? 'text-success' : 'text-danger' }}">
                        {{ ucfirst($t->direction) }}
                    </td>
                    <td class="text-end">{{ number_format($t->amount) }}</td>
                    <td class="text-end">{{ number_format($t->balance_after) }}</td>
                    <td><code>{{ $t->reference }}</code></td>
                    <td class="text-capitalize">{{ $t->status }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-3">No transactions.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@include('partials.pagination', ['paginator' => $transactions])
@endsection