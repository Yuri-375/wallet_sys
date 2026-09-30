@extends('layouts.app')

@section('title', 'Audit Logs')

@section('content')

<div class="container-fluid">

    {{-- ========================================================= --}}
    {{-- PAGE HEADER --}}
    {{-- ========================================================= --}}

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h1 class="h3 mb-1">
                Audit Logs
            </h1>

            <p class="text-muted mb-0">
                View activities performed by users, agents, and administrators.
            </p>
        </div>

        <div>
            <a
                href="{{ route('admin.audit-logs.index') }}"
                class="btn btn-outline-secondary"
            >
                <i class="bi bi-arrow-clockwise"></i>
                Reset
            </a>
        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- FILTERS --}}
    {{-- ========================================================= --}}

    <div class="card shadow-sm mb-4">

        <div class="card-header">

            <h5 class="mb-0">
                <i class="bi bi-funnel"></i>
                Filter Audit Logs
            </h5>

        </div>

        <div class="card-body">

            <form
                method="GET"
                action="{{ route('admin.audit-logs.index') }}"
            >

                <div class="row g-3">

                    {{-- Search --}}
                    <div class="col-md-4">

                        <label class="form-label">
                            Search
                        </label>

                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            value="{{ request('search') }}"
                            placeholder="Name, email, phone, action, IP..."
                        >

                    </div>


                    {{-- Actor --}}
                    <div class="col-md-3">

                        <label class="form-label">
                            Actor
                        </label>

                        <select
                            name="user_id"
                            class="form-select"
                        >

                            <option value="">
                                All Actors
                            </option>

                            @foreach($users as $user)

                                <option
                                    value="{{ $user->id }}"
                                    {{ request('user_id') == $user->id ? 'selected' : '' }}
                                >
                                    {{ $user->name }}
                                    -
                                    {{ $user->getRoleNames()->implode(', ') }}
                                </option>

                            @endforeach

                        </select>

                    </div>


                    {{-- Action --}}
                    <div class="col-md-3">

                        <label class="form-label">
                            Action
                        </label>

                        <select
                            name="action"
                            class="form-select"
                        >

                            <option value="">
                                All Actions
                            </option>

                            @foreach($actions as $action)

                                <option
                                    value="{{ $action }}"
                                    {{ request('action') == $action ? 'selected' : '' }}
                                >
                                    {{ ucwords(str_replace('.', ' ', $action)) }}
                                </option>

                            @endforeach

                        </select>

                    </div>


                    {{-- Search Button --}}
                    <div class="col-md-2 d-flex align-items-end">

                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                        >
                            <i class="bi bi-search"></i>
                            Search
                        </button>

                    </div>


                    {{-- Date From --}}
                    <div class="col-md-3">

                        <label class="form-label">
                            Date From
                        </label>

                        <input
                            type="date"
                            name="date_from"
                            class="form-control"
                            value="{{ request('date_from') }}"
                        >

                    </div>


                    {{-- Date To --}}
                    <div class="col-md-3">

                        <label class="form-label">
                            Date To
                        </label>

                        <input
                            type="date"
                            name="date_to"
                            class="form-control"
                            value="{{ request('date_to') }}"
                        >

                    </div>


                    {{-- Quick Filters --}}
                    <div class="col-md-6 d-flex align-items-end gap-2">

                        {{-- Today --}}
                        <a
                            href="{{ route('admin.audit-logs.index', [
                                'date_from' => now()->format('Y-m-d'),
                                'date_to' => now()->format('Y-m-d')
                            ]) }}"
                            class="btn btn-outline-primary"
                        >
                            Today
                        </a>


                        {{-- Yesterday --}}
                        <a
                            href="{{ route('admin.audit-logs.index', [
                                'date_from' => now()->subDay()->format('Y-m-d'),
                                'date_to' => now()->subDay()->format('Y-m-d')
                            ]) }}"
                            class="btn btn-outline-primary"
                        >
                            Yesterday
                        </a>


                        {{-- This Month --}}
                        <a
                            href="{{ route('admin.audit-logs.index', [
                                'date_from' => now()->startOfMonth()->format('Y-m-d'),
                                'date_to' => now()->endOfMonth()->format('Y-m-d')
                            ]) }}"
                            class="btn btn-outline-primary"
                        >
                            This Month
                        </a>


                        {{-- Last Month --}}
                        <a
                            href="{{ route('admin.audit-logs.index', [
                                'date_from' => now()->subMonth()->startOfMonth()->format('Y-m-d'),
                                'date_to' => now()->subMonth()->endOfMonth()->format('Y-m-d')
                            ]) }}"
                            class="btn btn-outline-primary"
                        >
                            Last Month
                        </a>

                    </div>

                </div>

            </form>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- AUDIT LOG TABLE --}}
    {{-- ========================================================= --}}

    <div class="card shadow-sm">

        <div class="card-header">

            <div class="d-flex justify-content-between align-items-center">

                <div>

                    <h5 class="mb-0">
                        Audit Activity
                    </h5>

                    <small class="text-muted">

                        Showing
                        {{ $auditLogs->firstItem() ?? 0 }}

                        -

                        {{ $auditLogs->lastItem() ?? 0 }}

                        of

                        {{ $auditLogs->total() }}

                        logs

                    </small>

                </div>

                <span class="badge bg-dark">

                    {{ number_format($auditLogs->total()) }}

                    Total

                </span>

            </div>

        </div>


        <div class="card-body p-0">

            @if($auditLogs->count() > 0)

                <div class="table-responsive">

                    <table class="table table-hover table-bordered align-middle mb-0">

                        <thead class="table-light">

                            <tr>

                                <th>
                                    #
                                </th>

                                <th>
                                    Date & Time
                                </th>

                                <th>
                                    Actor
                                </th>

                                <th>
                                    Role
                                </th>

                                <th>
                                    Action
                                </th>

                                <th>
                                    Description
                                </th>

                                <th>
                                    Target
                                </th>

                                <th>
                                    IP Address
                                </th>

                                <th>
                                    View
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @foreach($auditLogs as $auditLog)

                                <tr>

                                    {{-- ID --}}
                                    <td>
                                        {{ $auditLog->id }}
                                    </td>


                                    {{-- Date --}}
                                    <td>

                                        <strong>
                                            {{ $auditLog->created_at->format('Y-m-d') }}
                                        </strong>

                                        <br>

                                        <small class="text-muted">
                                            {{ $auditLog->created_at->format('H:i:s') }}
                                        </small>

                                    </td>


                                    {{-- Actor --}}
                                    <td>

                                        @if($auditLog->user)

                                            <strong>
                                                {{ $auditLog->user->name }}
                                            </strong>

                                            <br>

                                            <small class="text-muted">
                                                {{ $auditLog->user->email }}
                                            </small>

                                        @else

                                            <span class="text-muted">
                                                System
                                            </span>

                                        @endif

                                    </td>


                                    {{-- Role --}}
                                    <td>

                                        @if($auditLog->user)

                                            @foreach($auditLog->user->getRoleNames() as $role)

                                                @if($role === 'admin')

                                                    <span class="badge bg-danger">
                                                        Admin
                                                    </span>

                                                @elseif($role === 'agent')

                                                    <span class="badge bg-warning text-dark">
                                                        Agent
                                                    </span>

                                                @elseif($role === 'user')

                                                    <span class="badge bg-primary">
                                                        User
                                                    </span>

                                                @else

                                                    <span class="badge bg-secondary">
                                                        {{ ucfirst($role) }}
                                                    </span>

                                                @endif

                                            @endforeach

                                        @else

                                            <span class="text-muted">
                                                System
                                            </span>

                                        @endif

                                    </td>


                                    {{-- Action --}}
                                    <td>

                                        <strong>
                                            {{ $auditLog->action_label }}
                                        </strong>

                                        <br>

                                        <small class="text-muted">
                                            {{ $auditLog->action }}
                                        </small>

                                    </td>


                                    {{-- Description --}}
                                    <td>

                                        @if($auditLog->description)

                                            {{ \Illuminate\Support\Str::limit(
                                                $auditLog->description,
                                                80
                                            ) }}

                                        @else

                                            <span class="text-muted">
                                                -
                                            </span>

                                        @endif

                                    </td>


                                    {{-- Target --}}
                                    <td>

                                        @if($auditLog->auditable_type)

                                            <small>

                                                {{ class_basename(
                                                    $auditLog->auditable_type
                                                ) }}

                                                @if($auditLog->auditable_id)

                                                    #{{ $auditLog->auditable_id }}

                                                @endif

                                            </small>

                                        @else

                                            <span class="text-muted">
                                                -
                                            </span>

                                        @endif

                                    </td>


                                    {{-- IP Address --}}
                                    <td>

                                        <small>
                                            {{ $auditLog->ip_address ?? '-' }}
                                        </small>

                                    </td>


                                    {{-- View Button --}}
                                    <td>

                                        <a
                                            href="{{ route(
                                                'admin.audit-logs.show',
                                                $auditLog
                                            ) }}"
                                            class="btn btn-sm btn-outline-primary"
                                        >

                                            <i class="bi bi-eye"></i>

                                            View

                                        </a>

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            @else

                {{-- ================================================= --}}
                {{-- NO RECORDS --}}
                {{-- ================================================= --}}

                <div class="text-center py-5">

                    <div class="mb-3">

                        <i
                            class="bi bi-journal-x"
                            style="font-size: 3rem;"
                        ></i>

                    </div>

                    <h5>
                        No Audit Logs Found
                    </h5>

                    <p class="text-muted">
                        There are no audit records matching your filters.
                    </p>

                    <a
                        href="{{ route('admin.audit-logs.index') }}"
                        class="btn btn-outline-primary"
                    >
                        Clear Filters
                    </a>

                </div>

            @endif

        </div>


        {{-- ========================================================= --}}
        {{-- PAGINATION --}}
        {{-- ========================================================= --}}

        @if($auditLogs->hasPages())

            <div class="card-footer">

                <div class="d-flex justify-content-center">

                    {{ $auditLogs->links() }}

                </div>

            </div>

        @endif

    </div>

</div>
@include('partials.pagination', ['paginator' => $transactions])
@endsection
