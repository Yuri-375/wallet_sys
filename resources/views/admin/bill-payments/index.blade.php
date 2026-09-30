@extends('layouts.app')
@section('heading', 'All Bill Payments')

@section('content')
<form method="GET" class="mb-3">
    <select name="status" class="form-select" style="max-width:220px;" onchange="this.form.submit()">
        <option value="">All statuses</option>
        @foreach (['pending','processing','completed','failed','refunded'] as $s)
            <option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>
        @endforeach
    </select>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead><tr>
                <th>Date</th><th>User</th><th>Biller</th><th>Account</th>
                <th class="text-end">Amount</th><th>Status</th><th>Reference</th>
            </tr></thead>
            <tbody>
            @forelse ($payments as $p)
                <tr>
                    <td>{{ $p->created_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $p->user?->name }}</td>
                    <td>{{ $p->biller?->name }}</td>
                    <td>{{ $p->account_number }}</td>
                    <td class="text-end">{{ number_format($p->amount) }}</td>
                    <td class="text-capitalize">{{ $p->status }}</td>
                    <td><code>{{ $p->reference }}</code></td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-3">No payments.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@include('partials.pagination', ['paginator' => $payments])
@endsection