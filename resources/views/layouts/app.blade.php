<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Wallet') — {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .sidebar { min-height: 100vh; }
        .sidebar a { display:block; padding:.5rem 1rem; color:#222; text-decoration:none; border-radius:.25rem; }
        .sidebar a:hover { background:#f1f3f5; }
        .sidebar a.active { background:#0d6efd; color:#fff; }
        .stat-card { border-left: 4px solid #0d6efd; }
    </style>
</head>
<body class="bg-light">
<div class="d-flex">
    <aside class="sidebar bg-white border-end" style="width:250px;">
        @include('partials.sidebar')
    </aside>

    <main class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">@yield('heading')</h1>
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route(auth()->user()->getRoleNames()->first().'.notifications.index') }}"
                   class="position-relative text-decoration-none fs-4">
                    🔔
                    @php $unread = auth()->user()->unreadNotifications()->count(); @endphp
                    @if ($unread)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            {{ $unread }}
                        </span>
                    @endif
                </a>
                <span class="text-muted">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}" class="mb-0">
                    @csrf
                    <button class="btn btn-sm btn-outline-danger">Logout</button>
                </form>
            </div>
        </div>

        @include('partials.flash')

        @yield('content')
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>