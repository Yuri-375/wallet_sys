@extends('layouts.app')
@section('heading', 'Users')

@section('content')
<form method="GET" class="d-flex gap-2 mb-3">
    <input name="q" value="{{ request('q') }}" placeholder="Search name / email / phone"
           class="form-control" style="max-width:320px;">
    <select name="kyc" class="form-select" style="max-width:180px;">
        <option value="">All KYC</option>
        @foreach (['not_submitted','pending','approved','rejected'] as $s)
            <option value="{{ $s }}" @selected(request('kyc') === $s)>{{ $s }}</option>
        @endforeach
    </select>
    <button class="btn btn-outline-primary">Filter</button>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary ms-auto">New User</a>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead><tr>
                <th>Name</th><th>Phone</th><th>Email</th><th>KYC</th><th>Status</th>
                <th class="text-end">Balance</th><th></th>
            </tr></thead>
            <tbody>
            @forelse ($users as $u)
                <tr>
                    <td>{{ $u->name }}</td>
                    <td>{{ $u->phone }}</td>
                    <td>{{ $u->email }}</td>
                    <td class="text-capitalize">{{ $u->kyc_status }}</td>
                    <td class="text-capitalize">{{ $u->status }}</td>
                    <td class="text-end">{{ number_format($u->wallet->balance ?? 0) }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.users.show', $u) }}" class="btn btn-sm btn-outline-primary">View</a>
                        <a href="{{ route('admin.users.edit', $u) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-3">No users.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@include('partials.pagination', ['paginator' => $users])
@endsection