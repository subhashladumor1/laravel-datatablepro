<div class="dtable-pagination-wrapper">
    @if($totalPages > 1)
        <nav aria-label="Table pagination">
            <ul class="dtable-pagination-list">
                {{-- Previous --}}
                <li class="dtable-pagination-item {{ $currentPage === 1 ? 'disabled' : '' }}">
                    <a href="{{ $currentPage > 1 ? '?page=' . ($currentPage - 1) : '#' }}" 
                       class="dtable-pagination-link"
                       @if($currentPage === 1) aria-disabled="true" @endif>
                        Previous
                    </a>
                </li>

                {{-- First page --}}
                @if($startPage > 1)
                    <li class="dtable-pagination-item">
                        <a href="?page=1" class="dtable-pagination-link">1</a>
                    </li>
                    @if($startPage > 2)
                        <li class="dtable-pagination-item disabled">
                            <span class="dtable-pagination-ellipsis">...</span>
                        </li>
                    @endif
                @endif

                {{-- Page numbers --}}
                @for($i = $startPage; $i <= $endPage; $i++)
                    <li class="dtable-pagination-item {{ $i === $currentPage ? 'active' : '' }}">
                        <a href="?page={{ $i }}" 
                           class="dtable-pagination-link"
                           @if($i === $currentPage) aria-current="page" @endif>
                            {{ $i }}
                        </a>
                    </li>
                @endfor

                {{-- Last page --}}
                @if($endPage < $totalPages)
                    @if($endPage < $totalPages - 1)
                        <li class="dtable-pagination-item disabled">
                            <span class="dtable-pagination-ellipsis">...</span>
                        </li>
                    @endif
                    <li class="dtable-pagination-item">
                        <a href="?page={{ $totalPages }}" class="dtable-pagination-link">{{ $totalPages }}</a>
                    </li>
                @endif

                {{-- Next --}}
                <li class="dtable-pagination-item {{ $currentPage === $totalPages ? 'disabled' : '' }}">
                    <a href="{{ $currentPage < $totalPages ? '?page=' . ($currentPage + 1) : '#' }}" 
                       class="dtable-pagination-link"
                       @if($currentPage === $totalPages) aria-disabled="true" @endif>
                        Next
                    </a>
                </li>
            </ul>
        </nav>
    @endif
</div>
