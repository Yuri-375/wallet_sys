@extends('layouts.app')

@section('heading', 'KYC Review — ' . $user->name)

@section('content')

<div class="container">

    {{-- User Information --}}
    <div class="card mb-4">
        <div class="card-header fw-bold">
            User Information
        </div>

        <div class="card-body">

            <div class="row">

                <div class="col-md-6 mb-2">
                    <strong>Name:</strong>
                    {{ $user->name }}
                </div>

                <div class="col-md-6 mb-2">
                    <strong>Phone:</strong>
                    {{ $user->phone }}
                </div>

                <div class="col-md-6 mb-2">
                    <strong>Email:</strong>
                    {{ $user->email }}
                </div>

                <div class="col-md-6 mb-2">
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
                            {{ ucfirst($user->kyc_status) }}
                        </span>
                    @endif

                </div>

            </div>

        </div>
    </div>


    {{-- KYC Documents --}}
    <div class="card mb-4">

        <div class="card-header fw-bold">
            KYC Documents
        </div>

        <div class="card-body">

            @forelse ($documents as $d)

                <div class="border rounded p-3 mb-3">

                    {{-- Document Type --}}
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
                                {{ ucfirst($d->status) }}
                            </span>

                        @endif

                    </div>


                    {{-- Upload Date --}}
                    <div class="small text-muted mb-3">

                        Uploaded:
                        {{ $d->created_at->format('Y-m-d H:i') }}

                    </div>


                    {{-- View File --}}
                    <a href="{{ route('admin.kyc.document', [
                        'user' => $user->id,
                        'document' => $d->id,
                    ]) }}"
                       target="_blank"
                       class="btn btn-sm btn-outline-primary">

                        View File

                    </a>

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
                  action="{{ route('admin.kyc.approve', $user) }}">

                @csrf

                <button type="submit"
                        class="btn btn-success"
                        onclick="return confirm('Approve this KYC?')">

                    Approve

                </button>

            </form>

        @endif


        {{-- Reject --}}
        @if ($user->kyc_status !== 'rejected')

            <form method="POST"
                  action="{{ route('admin.kyc.reject', $user) }}"
                  class="d-flex gap-2">

                @csrf

                <input
                    name="reason"
                    class="form-control"
                    placeholder="Reason for rejection"
                    required
                >

                <button type="submit"
                        class="btn btn-danger">

                    Reject

                </button>

            </form>

        @endif

    </div>

</div>

@endsection

