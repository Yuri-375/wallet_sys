@extends('layouts.app')
@section('heading', 'Pay Bill')

@section('content')
<div class="alert alert-info">
    Balance: <strong>{{ number_format(auth()->user()->wallet->balance) }} MMK</strong>
</div>

<form method="POST" action="{{ route('user.bills.store') }}"
      class="card p-4" style="max-width:640px;">
    @csrf
    <input type="hidden" name="idempotency_key" value="{{ $idempotencyKey }}">

    <div class="mb-3">
        <label>Biller</label>
        <select name="biller_id" class="form-select" required>
            <option value="">— Select —</option>
            @foreach ($billers as $b)
                <option value="{{ $b->id }}" @selected(old('biller_id') == $b->id)>
                    {{ $b->name }} ({{ $b->category }})
                </option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label>Account / Customer Number</label>
        <input name="account_number" value="{{ old('account_number') }}" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Amount (MMK)</label>
        <input type="number" name="amount" min="1" value="{{ old('amount') }}" class="form-control" required>
    </div>

    <button class="btn btn-primary">Pay</button>
</form>
@endsection