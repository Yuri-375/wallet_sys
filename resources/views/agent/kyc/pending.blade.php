@extends('layouts.app')
@section('heading', 'Pending KYC')

@section('content')
<div class="card">
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead><tr>
                <th>Name</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Submitted</th><th></th>
            </tr></thead>
            <tbody>
            @forelse ($users as $u)
                <tr>
                    <td>{{ $u->name }}</td>
                    <td>{{ $u->phone }}</td>
                    <td>{{ $u->email }}</td>
                    <td>{{ $u->updated_at->format('Y-m-d H:i') }}</td>
                    <td><a href="{{ route('agent.kyc.show', $u) }}" class="btn btn-sm btn-primary">Review</a></td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-3">Nothing pending.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@include('partials.pagination', ['paginator' => $users])
@endsection