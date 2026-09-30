@extends('layouts.app')
@section('heading', 'Agent — '.$agent->name)

@section('content')
<div class="card mb-3"><div class="card-body">
    <div><strong>Phone:</strong> {{ $agent->phone }}</div>
    <div><strong>Email:</strong> {{ $agent->email }}</div>
    <div><strong>Status:</strong> {{ $agent->status }}</div>
    <div><strong>Assigned users:</strong> {{ \App\Models\User::where('assigned_agent_id', $agent->id)->count() }}</div>
</div></div>

<a href="{{ route('admin.agents.edit', $agent) }}" class="btn btn-outline-secondary">Edit</a>
@endsection