@if ($paginator->hasPages())
    <div class="pagination-container">
        <ul class="pagination-wrapper">
            {{-- Prev --}}
            @if ($paginator->onFirstPage())
                <li class="pagination-btn disabled">
                    <span><i data-feather="chevron-left"></i></span>
                </li>
            @else
                <li class="pagination-btn">
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev">
                        <i data-feather="chevron-left"></i>
                    </a>
                </li>
            @endif

            {{-- Pages --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="pagination-ellipsis">{{ $element }}</li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                            <a href="{{ $url }}">
                        <li class="pagination-page {{ $page == $paginator->currentPage() ? 'active' : '' }}">
                            {{ $page }}
                        </li>
                            </a>
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <li class="pagination-btn">
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next">
                        <i data-feather="chevron-right"></i>
                    </a>
                </li>
            @else
                <li class="pagination-btn disabled">
                    <span><i data-feather="chevron-right"></i></span>
                </li>
            @endif
        </ul>
    </div>
@endif
