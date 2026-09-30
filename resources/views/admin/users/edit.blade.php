@extends('layouts.app')
@section('heading', 'Edit User — '.$user->name)

@section('content')
<form method="POST" action="{{ route('admin.users.update', $user) }}" class="card p-4" style="max-width:520px;">
    @csrf @method('PUT')
    <div class="mb-3"><label>Name</label>
        <input name="name" value="{{ old('name', $user->name) }}" class="form-control" required></div>
    <div class="mb-3"><label>Phone</label>
        <input name="phone" value="{{ old('phone', $user->phone) }}" class="form-control" required></div>
    <div class="mb-3"><label>Status</label>
        <select name="status" class="form-select" required>
            @foreach (['active','suspended','blocked'] as $s)
                <option value="{{ $s }}" @selected($user->status === $s)>{{ $s }}</option>
            @endforeach
        </select></div>
    <button class="btn btn-primary">Save</button>
</form>
@endsection