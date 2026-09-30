@extends('layouts.app')
@section('heading', 'New Fraud Rule')

@section('content')
<form method="POST" action="{{ route('admin.fraud-rules.store') }}" class="card p-4" style="max-width:640px;">
    @csrf
    <div class="mb-3"><label>Code</label>
        <input name="code" value="{{ old('code') }}" class="form-control" required></div>
    <div class="mb-3"><label>Name</label>
        <input name="name" value="{{ old('name') }}" class="form-control" required></div>
    <div class="mb-3"><label>Description</label>
        <textarea name="description" class="form-control" required>{{ old('description') }}</textarea></div>
    <div class="mb-3"><label>Configuration Type</label>
        <select name="configuration_type" class="form-select" required>
            @foreach (['transfer_frequency','large_transfer_otp','new_receiver_high_amount','new_account_transfer','amount','large_debit'] as $t)
                <option value="{{ $t }}" @selected(old('configuration_type') === $t)>{{ $t }}</option>
            @endforeach
        </select></div>
    <div class="mb-3"><label>Threshold</label>
        <input type="number" name="threshold" value="{{ old('threshold') }}" class="form-control"></div>
    <div class="mb-3"><label>Severity</label>
        <select name="severity" class="form-select">
            <option value="">—</option>
            @foreach (['low','medium','high'] as $s)
                <option value="{{ $s }}" @selected(old('severity') === $s)>{{ $s }}</option>
            @endforeach
        </select></div>
    <div class="form-check mb-3">
        <input type="checkbox" name="is_active" value="1" class="form-check-input"
               @checked(old('is_active', true)) id="is_active">
        <label for="is_active" class="form-check-label">Active</label>
    </div>
    <button class="btn btn-primary">Create</button>
</form>
@endsection