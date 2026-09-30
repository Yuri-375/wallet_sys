@extends('layouts.app')
@section('heading', 'Statements')

@section('content')
<div class="card mb-3">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.statements.generate') }}" class="row g-2">
            @csrf
            <div class="col-md-4">
                <select name="user_id" class="form-select" required>
                    <option value="">— Select user —</option>
                    @foreach (\App\Models\User::role('user')->orderBy('name')->get() as $u)
                        <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3"><input type="date" name="period_start" class="form-control" required></div>
            <div class="col-md-3"><input type="date" name="period_end" class="form-control" required></div>
            <div class="col-md-2"><button class="btn btn-primary w-100">Generate</button></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead><tr>
                <th>User</th><th>Period</th>
                <th class="text-end">Opening</th><th class="text-end">Credits</th>
                <th class="text-end">Debits</th><th class="text-end">Closing</th>
                <th>Status</th>
            </tr></thead>
            <tbody>
            @forelse ($statements as $s)
                <tr>
                    <td>{{ $s->user?->name }}</td>
                    <td>{{ $s->period_start->format('Y-m-d') }} → {{ $s->period_end->format('Y-m-d') }}</td>
                    <td class="text-end">{{ number_format($s->opening_balance) }}</td>
                    <td class="text-end text-success">{{ number_format($s->total_credits) }}</td>
                    <td class="text-end text-danger">{{ number_format($s->total_debits) }}</td>
                    <td class="text-end">{{ number_format($s->closing_balance) }}</td>
                    <td class="text-capitalize">{{ $s->status }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-3">No statements.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@include('partials.pagination', ['paginator' => $statements])
@endsection