@if ($paginator->hasPages())
    <div class="pagination">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <button class="page-btn disabled" disabled aria-label="@lang('pagination.previous')">
                <span class="material-symbols-outlined" style="font-size: 16px;">chevron_left</span>
            </button>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="page-btn" rel="prev" aria-label="@lang('pagination.previous')">
                <span class="material-symbols-outlined" style="font-size: 16px;">chevron_left</span>
            </a>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <button class="page-btn disabled" disabled>{{ $element }}</button>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <button class="page-btn active" aria-current="page">{{ $page }}</button>
                    @else
                        <a href="{{ $url }}" class="page-btn">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="page-btn" rel="next" aria-label="@lang('pagination.next')">
                <span class="material-symbols-outlined" style="font-size: 16px;">chevron_right</span>
            </a>
        @else
            <button class="page-btn disabled" disabled aria-label="@lang('pagination.next')">
                <span class="material-symbols-outlined" style="font-size: 16px;">chevron_right</span>
            </button>
        @endif
    </div>
@endif
