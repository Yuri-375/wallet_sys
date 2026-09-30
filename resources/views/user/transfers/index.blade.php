@extends('layouts.app')
@section('heading', 'Transfers')

@section('content')
<div class="mb-3">
    <a href="{{ route('user.transfers.create') }}" class="btn btn-primary">Send Money</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead><tr>
                <th>Date</th><th>From</th><th>To</th>
                <th class="text-end">Amount</th><th>Status</th><th>Reference</th>
            </tr></thead>
            <tbody>
            @forelse ($transfers as $t)
                <tr>
                    <td>{{ $t->created_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $t->sender?->name }}</td>
                    <td>{{ $t->receiver?->name }}</td>
                    <td class="text-end">{{ number_format($t->amount) }}</td>
                    <td class="text-capitalize">{{ $t->status }}</td>
                    <td><code>{{ $t->reference }}</code></td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-3">No transfers yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@include('partials.pagination', ['paginator' => $transfers])
@endsection