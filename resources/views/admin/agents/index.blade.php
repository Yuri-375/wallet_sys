@extends('layouts.app')
@section('heading', 'Agents')

@section('content')
<a href="{{ route('admin.agents.create') }}" class="btn btn-primary mb-3">New Agent</a>

<div class="card">
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead><tr><th>Name</th><th>Phone</th><th>Email</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse ($agents as $a)
                <tr>
                    <td>{{ $a->name }}</td>
                    <td>{{ $a->phone }}</td>
                    <td>{{ $a->email }}</td>
                    <td class="text-capitalize">{{ $a->status }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.agents.show', $a) }}" class="btn btn-sm btn-outline-primary">View</a>
                        <a href="{{ route('admin.agents.edit', $a) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-3">No agents.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@include('partials.pagination', ['paginator' => $agents])
@endsection