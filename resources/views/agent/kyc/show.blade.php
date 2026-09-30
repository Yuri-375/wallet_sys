@extends('layouts.app')

@section('content')

<div class="container">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">KYC Verification</h3>
            <div class="text-muted">
                Review KYC documents for {{ $user->name }}
            </div>
        </div>

        <a href="{{ route('agent.kyc.pending') }}"
           class="btn btn-outline-secondary">
            Back to Pending KYC
        </a>
    </div>


    {{-- User Information --}}
    <div class="card mb-4">
        <div class="card-header">
            <strong>User Information</strong>
        </div>

        <div class="card-body">

            <div class="row">

                <div class="col-md-6 mb-3">
                    <strong>Name:</strong>
                    {{ $user->name }}
                </div>

                <div class="col-md-6 mb-3">
                    <strong>Email:</strong>
                    {{ $user->email }}
                </div>

                <div class="col-md-6 mb-3">
                    <strong>Phone:</strong>
                    {{ $user->phone }}
                </div>

                <div class="col-md-6 mb-3">
                    <strong>KYC Status:</strong>

                    @if ($user->kyc_status === 'approved')
                        <span class="badge bg-success">
                            Approved
                        </span>
                    @elseif ($user->kyc_status === 'rejected')
                        <span class="badge bg-danger">
                            Rejected
                        </span>
                    @elseif ($user->kyc_status === 'pending')
                        <span class="badge bg-warning text-dark">
                            Pending
                        </span>
                    @else
                        <span class="badge bg-secondary">
                            {{ $user->kyc_status }}
                        </span>
                    @endif

                </div>

                <div class="col-md-6 mb-3">
                    <strong>Account Status:</strong>

                    @if ($user->status === 'active')
                        <span class="badge bg-success">
                            Active
                        </span>
                    @else
                        <span class="badge bg-secondary">
                            {{ ucfirst($user->status) }}
                        </span>
                    @endif
                </div>

                <div class="col-md-6 mb-3">
                    <strong>Registered:</strong>
                    {{ $user->created_at->format('Y-m-d H:i') }}
                </div>

            </div>

        </div>
    </div>


    {{-- KYC Documents --}}
    <div class="card mb-4">

        <div class="card-header">
            <strong>KYC Documents</strong>
        </div>

        <div class="card-body">

            @forelse ($documents as $d)

                <div class="border rounded p-3 mb-3">

                    <div class="fw-bold mb-1">
                        {{ $d->document_type }}

                        @if ($d->status === 'approved')
                            <span class="badge bg-success">
                                Approved
                            </span>
                        @elseif ($d->status === 'rejected')
                            <span class="badge bg-danger">
                                Rejected
                            </span>
                        @elseif ($d->status === 'pending')
                            <span class="badge bg-warning text-dark">
                                Pending
                            </span>
                        @else
                            <span class="badge bg-secondary">
                                {{ $d->status }}
                            </span>
                        @endif
                    </div>

                    <div class="small text-muted mb-3">
                        Uploaded:
                        {{ $d->created_at->format('Y-m-d H:i') }}
                    </div>

                    {{-- View Document --}}
                    <div>
                        <a href="{{ route('agent.kyc.document', [
                            'user' => $user,
                            'document' => $d,
                        ]) }}"
                           target="_blank"
                           class="btn btn-sm btn-outline-primary">

                            View File

                        </a>
                    </div>

                </div>

            @empty

                <div class="text-muted">
                    No documents uploaded.
                </div>

            @endforelse

        </div>
    </div>


    {{-- KYC Actions --}}
    <div class="d-flex gap-2">

        {{-- Approve --}}
        @if ($user->kyc_status !== 'approved')

            <form method="POST"
                  action="{{ route('agent.kyc.approve', $user) }}">

                @csrf

                <button type="submit"
                        class="btn btn-success"
                        onclick="return confirm('Are you sure you want to approve this KYC?')">

                    Approve KYC

                </button>

            </form>

        @endif


        {{-- Reject --}}
        @if ($user->kyc_status !== 'rejected')

            <form method="POST"
                  action="{{ route('agent.kyc.reject', $user) }}">

                @csrf

                <button type="submit"
                        class="btn btn-danger"
                        onclick="return confirm('Are you sure you want to reject this KYC?')">

                    Reject KYC

                </button>

            </form>

        @endif

    </div>

</div>

@endsection