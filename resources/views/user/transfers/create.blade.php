@extends('layouts.app')
@section('heading', 'Send Money')

@section('content')
<div class="alert alert-info">
    Your balance: <strong>{{ number_format(auth()->user()->wallet->balance) }} MMK</strong>
</div>

<form method="POST" action="{{ route('user.transfers.store') }}"
      class="card p-4" style="max-width:640px;">
    @csrf
    <input type="hidden" name="idempotency_key" value="{{ $idempotencyKey }}">

    <div class="mb-3">
        <label>Recipient</label>
        <select name="receiver_id" class="form-select" required>
            <option value="">— Select —</option>
            @foreach ($recipients as $u)
                <option value="{{ $u->id }}" @selected(old('receiver_id') == $u->id)>
                    {{ $u->name }} ({{ $u->phone }})
                </option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label>Amount (MMK)</label>
        <input type="number" name="amount" min="1" value="{{ old('amount') }}"
               class="form-control" required>
        <div class="form-text">
            Transfers over 50,000 MMK require OTP verification.
        </div>
    </div>

    <button class="btn btn-primary">Send</button>
</form>
@endsection