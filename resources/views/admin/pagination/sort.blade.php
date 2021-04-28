<a href="{{ route(Route::current()->getAction('as')) }}?page=1&sort={{ $sortId }}&direction={{ $sort === $sortId && $direction === 'asc' ? 'desc' : 'asc' }}">
    {{ $sortName }}
    @if ($sort === $sortId)
        <i class="fas {{ $direction == 'asc' ? 'fa-sort-down' : 'fa-sort-up' }}"></i>
    @else
        <i class="fas fa-sort"></i>
    @endif
</a>
