@extends('layouts.app')
@section('heading', 'Billers')

@section('content')
<a href="{{ route('admin.billers.create') }}" class="btn btn-primary mb-3">New Biller</a>

<div class="card">
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead><tr>
                <th>Name</th><th>Code</th><th>Category</th><th>Active</th><th></th>
            </tr></thead>
            <tbody>
            @forelse ($billers as $b)
                <tr>
                    <td>{{ $b->name }}</td>
                    <td><code>{{ $b->code }}</code></td>
                    <td>{{ $b->category }}</td>
                    <td>
                        @if ($b->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('admin.billers.edit', $b) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-3">No billers.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@include('partials.pagination', ['paginator' => $billers])
@endsection