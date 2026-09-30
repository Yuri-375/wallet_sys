@extends('layouts.app')
@section('heading', 'Fraud Rules')

@section('content')
<a href="{{ route('admin.fraud-rules.create') }}" class="btn btn-primary mb-3">New Rule</a>

<div class="card">
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead><tr>
                <th>Code</th><th>Name</th><th>Type</th>
                <th class="text-end">Threshold</th><th>Active</th><th></th>
            </tr></thead>
            <tbody>
            @forelse ($rules as $r)
                <tr>
                    <td><code>{{ $r->code }}</code></td>
                    <td>{{ $r->name }}</td>
                    <td>{{ $r->configuration_type }}</td>
                    <td class="text-end">{{ $r->threshold !== null ? number_format($r->threshold) : '—' }}</td>
                    <td>
                        <span class="badge bg-{{ $r->is_active ? 'success' : 'secondary' }}">
                            {{ $r->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('admin.fraud-rules.edit', $r) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-3">No rules.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@include('partials.pagination', ['paginator' => $rules])
@endsection