@extends('layouts.app')
@section('heading', 'Edit Biller — '.$biller->name)

@section('content')
<form method="POST" action="{{ route('admin.billers.update', $biller) }}" class="card p-4" style="max-width:560px;">
    @csrf @method('PUT')
    <div class="mb-3"><label>Name</label>
        <input name="name" value="{{ old('name', $biller->name) }}" class="form-control" required></div>
    <div class="mb-3"><label>Code</label>
        <input name="code" value="{{ old('code', $biller->code) }}" class="form-control" required></div>
    <div class="mb-3"><label>Category</label>
        <input name="category" value="{{ old('category', $biller->category) }}" class="form-control" required></div>
    <div class="mb-3"><label>API URL</label>
        <input name="api_url" value="{{ old('api_url', $biller->api_url) }}" class="form-control"></div>
    <div class="mb-3"><label>Description</label>
        <textarea name="description" class="form-control">{{ old('description', $biller->description) }}</textarea></div>
    <div class="form-check mb-3">
        <input type="checkbox" name="is_active" value="1" class="form-check-input"
               @checked(old('is_active', $biller->is_active)) id="is_active">
        <label for="is_active" class="form-check-label">Active</label>
    </div>
    <button class="btn btn-primary">Save</button>
</form>
@endsection