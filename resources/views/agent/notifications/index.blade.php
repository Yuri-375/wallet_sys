@extends('layouts.app')
@section('heading', 'Notifications')

@section('content')
<form method="POST" action="{{ route('agent.notifications.read-all') }}" class="mb-3">
    @csrf
    <button class="btn btn-sm btn-outline-secondary">Mark all as read</button>
</form>

<div class="card">
    <ul class="list-group list-group-flush">
        @forelse ($notifications as $n)
            <li class="list-group-item d-flex justify-content-between align-items-start {{ $n->read_at ? '' : 'bg-light' }}">
                <div>
                    <div class="fw-semibold">{{ $n->data['type'] ?? 'notification' }}</div>
                    <div class="small text-muted">
                        {{ $n->created_at->diffForHumans() }}
                    </div>
                    <pre class="small mb-0">{{ json_encode($n->data, JSON_PRETTY_PRINT) }}</pre>
                </div>
                @if (! $n->read_at)
                    <form method="POST" action="{{ route('agent.notifications.read', $n->id) }}">
                        @csrf
                        <button class="btn btn-sm btn-outline-primary">Mark read</button>
                    </form>
                @endif
            </li>
        @empty
            <li class="list-group-item text-center text-muted py-3">No notifications.</li>
        @endforelse
    </ul>
</div>

@include('partials.pagination', ['paginator' => $notifications])
@endsection