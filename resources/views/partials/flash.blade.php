@foreach (['success' => 'success', 'warning' => 'warning', 'error' => 'danger', 'message' => 'info'] as $key => $type)
    @if (session($key))
        <div class="alert alert-{{ $type }}">{{ session($key) }}</div>
    @endif
@endforeach

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif