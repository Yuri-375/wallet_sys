@extends('layouts.app')

@section('content')

<div class="container-fluid py-4">

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">Notifications</h2>
        <p class="text-muted mb-0">
            View your notifications and mark them as read.
        </p>
    </div>
</div>

{{-- Success Message --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}

        <button type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Close">
        </button>
    </div>
@endif

{{-- Notifications --}}
<div class="card shadow-sm">

    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            All Notifications
        </h5>

        <span class="badge bg-primary">
            {{ $notifications->total() }}
        </span>
    </div>

    <div class="card-body p-0">

        @if($notifications->count() > 0)

            <div class="list-group list-group-flush">

                @foreach($notifications as $notification)

                    <div class="list-group-item
                        {{ is_null($notification->read_at) ? 'bg-light' : '' }}">

                        <div class="d-flex justify-content-between align-items-start">

                            <div class="d-flex">

                                {{-- Notification Icon --}}
                                <div class="me-3">
                                    @if(is_null($notification->read_at))
                                        <span class="badge bg-primary rounded-circle p-2">
                                            <i class="bi bi-bell-fill"></i>
                                        </span>
                                    @else
                                        <span class="badge bg-secondary rounded-circle p-2">
                                            <i class="bi bi-bell"></i>
                                        </span>
                                    @endif
                                </div>

                                {{-- Notification Content --}}
                                <div>

                                    <h6 class="mb-1">
                                        {{ $notification->data['title'] ?? 'Notification' }}

                                        @if(is_null($notification->read_at))
                                            <span class="badge bg-primary ms-2">
                                                New
                                            </span>
                                        @endif
                                    </h6>

                                    <p class="mb-1 text-muted">
                                        {{ $notification->data['message'] ?? 'You have a new notification.' }}
                                    </p>

                                    <small class="text-muted">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </small>

                                </div>

                            </div>

                            {{-- Mark as Read --}}
                            @if(is_null($notification->read_at))

                                <form action="{{ route('admin.notifications.read', $notification->id) }}"
                                      method="POST">

                                    @csrf
                                    @method('PATCH')

                                    <button type="submit"
                                            class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-check2"></i>
                                        Mark as read
                                    </button>

                                </form>

                            @else

                                <span class="text-muted small">
                                    <i class="bi bi-check2-all"></i>
                                    Read
                                </span>

                            @endif

                        </div>

                    </div>

                @endforeach

            </div>

        @else

            {{-- Empty State --}}
            <div class="text-center py-5">

                <div class="mb-3">
                    <i class="bi bi-bell-slash fs-1 text-muted"></i>
                </div>

                <h5>No notifications</h5>

                <p class="text-muted mb-0">
                    You don't have any notifications yet.
                </p>

            </div>

        @endif

    </div>

    {{-- Pagination --}}
    @if($notifications->hasPages())

        <div class="card-footer">
            {{ $notifications->links() }}
        </div>

    @endif

</div>
</div>
@endsection
