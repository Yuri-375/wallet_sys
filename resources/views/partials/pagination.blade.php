@if ($paginator->hasPages())
    <div class="mt-3">
        {{ $paginator->links('pagination::bootstrap-5') }}
    </div>
@endif