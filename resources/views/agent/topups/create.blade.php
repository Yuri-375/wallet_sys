@extends('layouts.app')
@section('heading', 'Cash Top-Up')

@section('content')
<form method="POST" action="{{ route('agent.topups.store') }}"
      class="card p-4" style="max-width:520px;">
    @csrf
    <input type="hidden" name="idempotency_key" value="{{ $idempotencyKey }}">

    <div class="mb-3">
        <label>Customer</label>
        <select name="user_id" class="form-select" required>
            <option value="">— Select —</option>
            @foreach ($users as $u)
                <option value="{{ $u->id }}"
                    @selected(old('user_id', $preselectedId) == $u->id)>
                    {{ $u->name }} ({{ $u->phone }}) — balance {{ number_format($u->wallet->balance ?? 0) }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label>Amount Received (MMK)</label>
        <input type="number" name="amount" min="1" value="{{ old('amount') }}" class="form-control" required>
        <div class="form-text">Confirm cash has been physically received.</div>
    </div>

    <button class="btn btn-primary" onclick="return confirm('Confirm you received the cash?')">
        Record Top-Up
    </button>
</form>
@endsection