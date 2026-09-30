@extends('layouts.app')
@section('heading', 'OTP Verification')

@section('content')
@if (session('mock_otp'))
    <div class="alert alert-warning">
        <strong>MOCK OTP:</strong> {{ session('mock_otp') }}
    </div>
@endif

<form method="POST" action="{{ route('user.transfers.otp.verify', $transfer) }}"
      class="card p-4" style="max-width:480px;">
    @csrf

    <p>Enter the 6-digit OTP sent for the transfer of
        <strong>{{ number_format($transfer->amount) }} MMK</strong>.</p>

    <div class="mb-3">
        <input name="otp" maxlength="6" pattern="\d{6}" class="form-control form-control-lg text-center" required>
    </div>

    <button class="btn btn-primary w-100">Verify &amp; Send</button>
</form>

<p class="text-muted mt-3">
    Expires at: {{ $transfer->otp_expires_at->format('H:i:s') }}
</p>
@endsection