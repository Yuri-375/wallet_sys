@extends('layouts.app')
@section('heading', 'Bill Payments')

@section('content')
<div class="mb-3">
    <a href="{{ route('user.bills.create') }}" class="btn btn-primary">Pay Bill</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead><tr>
                <th>Date</th><th>Biller</th><th>Account</th>
                <th class="text-end">Amount</th><th>Status</th><th>Reference</th>
            </tr></thead>
            <tbody>
            @forelse ($payments as $p)
                <tr>
                    <td>{{ $p->created_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $p->biller?->name }}</td>
                    <td>{{ $p->account_number }}</td>
                    <td class="text-end">{{ number_format($p->amount) }}</td>
                    <td class="text-capitalize">{{ $p->status }}</td>
                    <td><code>{{ $p->reference }}</code></td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-3">No payments yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@include('partials.pagination', ['paginator' => $payments])
@endsection