@extends('layouts.app')
@section('heading', 'Wallets')

@section('content')
<form method="GET" class="d-flex gap-2 mb-3">
    <input name="q" value="{{ request('q') }}" placeholder="Search user"
           class="form-control" style="max-width:320px;">
    <select name="frozen" class="form-select" style="max-width:180px;">
        <option value="">Any status</option>
        <option value="1" @selected(request('frozen') === '1')>Frozen</option>
        <option value="0" @selected(request('frozen') === '0')>Active</option>
    </select>
    <button class="btn btn-outline-primary">Filter</button>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead><tr>
                <th>User</th><th>Email</th>
                <th class="text-end">Balance</th>
                <th>Status</th><th></th>
            </tr></thead>
            <tbody>
            @forelse ($wallets as $w)
                <tr>
                    <td>{{ $w->user?->name }}</td>
                    <td>{{ $w->user?->email }}</td>
                    <td class="text-end">{{ number_format($w->balance) }} {{ $w->currency }}</td>
                    <td>
                        @if ($w->is_frozen)
                            <span class="badge bg-danger">Frozen</span>
                        @else
                            <span class="badge bg-success">Active</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('admin.wallets.show', $w) }}" class="btn btn-sm btn-outline-primary">View</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-3">No wallets.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@include('partials.pagination', ['paginator' => $wallets])
@endsection