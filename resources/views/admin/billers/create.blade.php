@extends('layouts.app')
@section('heading', 'New Biller')

@section('content')
<form method="POST" action="{{ route('admin.billers.store') }}" class="card p-4" style="max-width:560px;">
    @csrf
    <div class="mb-3"><label>Name</label>
        <input name="name" value="{{ old('name') }}" class="form-control" required></div>
    <div class="mb-3"><label>Code</label>
        <input name="code" value="{{ old('code') }}" class="form-control" required></div>
    <div class="mb-3"><label>Category</label>
        <input name="category" value="{{ old('category') }}" class="form-control" required></div>
    <div class="mb-3"><label>API URL</label>
        <input name="api_url" value="{{ old('api_url') }}" class="form-control"></div>
    <div class="mb-3"><label>Description</label>
        <textarea name="description" class="form-control">{{ old('description') }}</textarea></div>
    <div class="form-check mb-3">
        <input type="checkbox" name="is_active" value="1" class="form-check-input"
               @checked(old('is_active', true)) id="is_active">
        <label for="is_active" class="form-check-label">Active</label>
    </div>
    <button class="btn btn-primary">Create</button>
</form>
@endsection