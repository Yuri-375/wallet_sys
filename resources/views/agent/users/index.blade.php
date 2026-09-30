@extends('layouts.app')
@section('heading', 'Assigned Users')

@section('content')
<div class="card">
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead><tr>
                <th>Name</th><th>Phone</th><th>Email</th>
                <th>KYC</th><th>Status</th>
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
                    <td><a href="{{ route('agent.users.show', $u) }}" class="btn btn-sm btn-outline-primary">View</a></td>
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