@extends('layouts.app')

@section('title', 'Audit Log Details')

@section('content')

<div class="container-fluid">

    {{-- ================================================================
         HEADER
    ================================================================= --}}

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h1 class="h3 mb-1">
                Audit Log Details
            </h1>

            <p class="text-muted mb-0">
                Detailed business activity record
            </p>

        </div>

        <a
            href="{{ route('admin.audit-logs.index') }}"
            class="btn btn-secondary"
        >
            ← Back to Audit Logs
        </a>

    </div>


    {{-- ================================================================
         BASIC INFORMATION
    ================================================================= --}}

    <div class="card shadow-sm mb-4">

        <div class="card-header">
            <strong>Audit Information</strong>
        </div>

        <div class="card-body">

            <div class="row g-4">

                {{-- ID --}}

                <div class="col-md-3">

                    <strong>
                        Audit ID
                    </strong>

                    <div class="mt-1">
                        #{{ $auditLog->id }}
                    </div>

                </div>


                {{-- Date --}}

                <div class="col-md-3">

                    <strong>
                        Date & Time
                    </strong>

                    <div class="mt-1">
                        {{ $auditLog->created_at->format('Y-m-d H:i:s') }}
                    </div>

                </div>


                {{-- Action --}}

                <div class="col-md-3">

                    <strong>
                        Action
                    </strong>

                    <div class="mt-1">

                        <span class="badge bg-primary">
                            {{ $auditLog->action_label }}
                        </span>

                    </div>

                </div>


                {{-- IP --}}

                <div class="col-md-3">

                    <strong>
                        IP Address
                    </strong>

                    <div class="mt-1">
                        {{ $auditLog->ip_address ?? '—' }}
                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- ================================================================
         ACTOR
    ================================================================= --}}

    <div class="card shadow-sm mb-4">

        <div class="card-header">
            <strong>Actor</strong>
        </div>

        <div class="card-body">

            @if($auditLog->user)

                <div class="row g-4">

                    <div class="col-md-4">

                        <strong>
                            Name
                        </strong>

                        <div class="mt-1">
                            {{ $auditLog->user->name }}
                        </div>

                    </div>


                    <div class="col-md-4">

                        <strong>
                            Email
                        </strong>

                        <div class="mt-1">
                            {{ $auditLog->user->email }}
                        </div>

                    </div>


                    <div class="col-md-4">

                        <strong>
                            User ID
                        </strong>

                        <div class="mt-1">
                            #{{ $auditLog->user->id }}
                        </div>

                    </div>

                </div>

            @else

                <span class="text-muted">
                    System action
                </span>

            @endif

        </div>

    </div>


    {{-- ================================================================
         DESCRIPTION
    ================================================================= --}}

    <div class="card shadow-sm mb-4">

        <div class="card-header">
            <strong>Description</strong>
        </div>

        <div class="card-body">

            @if($auditLog->description)

                <p class="mb-0">
                    {{ $auditLog->description }}
                </p>

            @else

                <span class="text-muted">
                    No description provided.
                </span>

            @endif

        </div>

    </div>


    {{-- ================================================================
         AUDITABLE TARGET
    ================================================================= --}}

    <div class="card shadow-sm mb-4">

        <div class="card-header">
            <strong>Target Object</strong>
        </div>

        <div class="card-body">

            @if($auditLog->auditable_type)

                @php

                    $targetName = class_basename(
                        $auditLog->auditable_type
                    );

                @endphp

                <div class="row g-4">

                    <div class="col-md-6">

                        <strong>
                            Object Type
                        </strong>

                        <div class="mt-1">
                            {{ $targetName }}
                        </div>

                    </div>


                    <div class="col-md-6">

                        <strong>
                            Object ID
                        </strong>

                        <div class="mt-1">

                            @if($auditLog->auditable_id)

                                #{{ $auditLog->auditable_id }}

                            @else

                                —

                            @endif

                        </div>

                    </div>

                </div>

            @else

                <span class="text-muted">
                    No target object.
                </span>

            @endif

        </div>

    </div>


    {{-- ================================================================
         OLD VALUES
    ================================================================= --}}

    <div class="card shadow-sm mb-4">

        <div class="card-header">
            <strong>Old Values</strong>
        </div>

        <div class="card-body">

            @if(!empty($auditLog->old_values))

                <pre class="bg-light p-3 rounded mb-0"><code>{{ json_encode(
    $auditLog->old_values,
    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
) }}</code></pre>

            @else

                <span class="text-muted">
                    No previous values recorded.
                </span>

            @endif

        </div>

    </div>


    {{-- ================================================================
         NEW VALUES
    ================================================================= --}}

    <div class="card shadow-sm mb-4">

        <div class="card-header">
            <strong>New Values</strong>
        </div>

        <div class="card-body">

            @if(!empty($auditLog->new_values))

                <pre class="bg-light p-3 rounded mb-0"><code>{{ json_encode(
    $auditLog->new_values,
    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
) }}</code></pre>

            @else

                <span class="text-muted">
                    No new values recorded.
                </span>

            @endif

        </div>

    </div>


    {{-- ================================================================
         REQUEST INFORMATION
    ================================================================= --}}

    <div class="card shadow-sm mb-4">

        <div class="card-header">
            <strong>Request Information</strong>
        </div>

        <div class="card-body">

            <div class="row g-4">

                <div class="col-md-6">

                    <strong>
                        IP Address
                    </strong>

                    <div class="mt-1">
                        {{ $auditLog->ip_address ?? '—' }}
                    </div>

                </div>


                <div class="col-md-6">

                    <strong>
                        User Agent
                    </strong>

                    <div class="mt-1 text-break">
                        {{ $auditLog->user_agent ?? '—' }}
                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- ================================================================
         FOOTER
    ================================================================= --}}

    <div class="mb-4">

        <a
            href="{{ route('admin.audit-logs.index') }}"
            class="btn btn-secondary"
        >
            ← Back to Audit Logs
        </a>

    </div>

</div>

@endsection