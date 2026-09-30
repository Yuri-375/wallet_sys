@extends('layouts.app')
@section('heading', 'All Transactions')

@section('content')
<form method="GET" class="row g-2 mb-3">
    <div class="col-auto">
        <select name="type" class="form-select">
            <option value="">All types</option>
            @foreach (['transfer','top_up','bill_payment','bill_refund'] as $t)
                <option value="{{ $t }}" @selected(request('type') === $t)>{{ $t }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto">
        <select name="direction" class="form-select">
            <option value="">Any</option>
            <option value="credit" @selected(request('direction') === 'credit')>Credit</option>
            <option value="debit" @selected(request('direction') === 'debit')>Debit</option>
        </select>
    </div>
    <div class="col-auto">
        <input name="reference" value="{{ request('reference') }}" placeholder="Reference"
               class="form-control">
    </div>
    <div class="col-auto"><button class="btn btn-outline-primary">Filter</button></div>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead><tr>
                <th>Date</th><th>User</th><th>Type</th><th>Direction</th>
                <th class="text-end">Amount</th><th class="text-end">Balance After</th>
                <th>Reference</th>
            </tr></thead>
            <tbody>
            @forelse ($transactions as $t)
                <tr>
                    <td>{{ $t->created_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $t->wallet?->user?->name }}</td>
                    <td>{{ $t->type }}</td>
                    <td class="{{ $t->direction === 'credit' ? 'text-success' : 'text-danger' }}">
                        {{ $t->direction }}
                    </td>
                    <td class="text-end">{{ number_format($t->amount) }}</td>
                    <td class="text-end">{{ number_format($t->balance_after) }}</td>
                    <td><code>{{ $t->reference }}</code></td>
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