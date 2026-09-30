@extends('layouts.app')
@section('heading', 'KYC Verification')

@section('content')
<div class="alert alert-info">
    Current status: <strong class="text-capitalize">{{ auth()->user()->kyc_status }}</strong>
</div>

<div class="card mb-4">
    <div class="card-header fw-bold">Submit Document</div>
    <div class="card-body">
        <form method="POST" action="{{ route('user.kyc.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label>Document Type</label>
                <select name="document_type" class="form-select" required>
                    <option value="national_id">National ID</option>
                    <option value="passport">Passport</option>
                    <option value="driving_license">Driving License</option>
                </select>
            </div>
            <div class="mb-3">
                <label>File (jpg, png, pdf, ≤5 MB)</label>
                <input type="file" name="document" class="form-control" required>
            </div>
            <button class="btn btn-primary">Submit</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header fw-bold">My Documents</div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead><tr><th>Type</th><th>Status</th><th>Submitted</th><th>Reason</th></tr></thead>
            <tbody>
            @forelse ($documents as $d)
                <tr>
                    <td>{{ $d->document_type }}</td>
                    <td class="text-capitalize">{{ $d->status }}</td>
                    <td>{{ $d->created_at->format('Y-m-d') }}</td>
                    <td>{{ $d->rejection_reason }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted py-3">No documents.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection