@extends('layouts.app')
@section('heading', 'New User')

@section('content')
<form method="POST" action="{{ route('admin.users.store') }}" class="card p-4" style="max-width:520px;">
    @csrf
    <div class="mb-3"><label>Name</label>
        <input name="name" value="{{ old('name') }}" class="form-control" required></div>
    <div class="mb-3"><label>Email</label>
        <input name="email" type="email" value="{{ old('email') }}" class="form-control" required></div>
    <div class="mb-3"><label>Phone</label>
        <input name="phone" value="{{ old('phone') }}" class="form-control" required></div>
    <div class="mb-3"><label>Password</label>
        <input name="password" type="password" class="form-control" required></div>
    <button class="btn btn-primary">Create</button>
</form>
@endsection