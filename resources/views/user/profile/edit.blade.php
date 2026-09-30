@extends('layouts.app')
@section('heading', 'My Profile')

@section('content')
<form method="POST" action="{{ route('user.profile.update') }}" class="card p-4" style="max-width:560px;">
    @csrf @method('PUT')

    <div class="mb-3">
        <label>Name</label>
        <input name="name" value="{{ old('name', $user->name) }}" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Email (read-only)</label>
        <input value="{{ $user->email }}" class="form-control" disabled>
    </div>
    <div class="mb-3">
        <label>Phone</label>
        <input name="phone" value="{{ old('phone', $user->phone) }}" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>New Password (optional)</label>
        <input type="password" name="password" class="form-control">
    </div>
    <div class="mb-3">
        <label>Confirm Password</label>
        <input type="password" name="password_confirmation" class="form-control">
    </div>
    <button class="btn btn-primary">Save</button>
</form>
@endsection