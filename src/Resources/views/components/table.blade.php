<div id="{{ $id }}" class="dtable-component">
    @if($filters && count($filters) > 0)
        <div class="dtable-mobile-filter-toggle">
            <button type="button" onclick="this.nextElementSibling.classList.toggle('active')">
                Filters
            </button>
        </div>
    @endif
</div>

@push('styles')
@if(file_exists(public_path('vendor/dtable/css/dtable-styles.css')))
    <link rel="stylesheet" href="{{ asset('vendor/dtable/css/dtable-styles.css') }}">
@else
    {{-- Fallback to raw SCSS compiled or inline styles --}}
    <link rel="stylesheet" href="{{ asset('vendor/dtable/raw/scss/dtable.scss') }}">
@endif
@endpush

@push('scripts')
@if(file_exists(public_path('vendor/dtable/js/dtable.js')))
    {{-- Use built assets --}}
    <script src="{{ asset('vendor/dtable/js/dtable.js') }}"></script>
    <script src="{{ asset('vendor/dtable/js/dtable-renderers.js') }}"></script>
    @if($virtualScroll)
    <script src="{{ asset('vendor/dtable/js/virtual-scroll.js') }}"></script>
    @endif
    @if($realtime)
    <script src="{{ asset('vendor/dtable/js/realtime.js') }}"></script>
    @endif
    @if($exportUrl)
    <script src="{{ asset('vendor/dtable/js/image-export.js') }}"></script>
    @endif
@else
    {{-- Use raw assets --}}
    <script src="{{ asset('vendor/dtable/raw/js/dtable.core.js') }}"></script>
    <script src="{{ asset('vendor/dtable/raw/js/dtable.renderers.js') }}"></script>
    @if($virtualScroll)
    <script src="{{ asset('vendor/dtable/raw/js/dtable.plugins/virtual-scroll.js') }}"></script>
    @endif
    @if($realtime)
    <script src="{{ asset('vendor/dtable/raw/js/dtable.plugins/realtime.js') }}"></script>
    @endif
    @if($exportUrl)
    <script src="{{ asset('vendor/dtable/raw/js/exporters/image-export.js') }}"></script>
    @endif
@endif
@if($exportUrl)
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    const table = DTable.init('#{{ $id }}', {
        ajax: '{{ $ajax }}',
        columns: @json($columns),
        pageLength: {{ $pageLength }},
        responsive: {{ $responsive ? 'true' : 'false' }},
        persistKey: {{ $persistKey ? "'" . $persistKey . "'" : 'null' }},
        exportUrl: {{ $exportUrl ? "'" . $exportUrl . "'" : 'null' }},
        filters: @json($filters),
        virtualScroll: {{ $virtualScroll ? 'true' : 'false' }},
        realtime: {{ $realtime ? 'true' : 'false' }}
    });

    @if($virtualScroll)
    table.registerPlugin('virtualScroll', DTableVirtualScroll);
    @endif

    @if($realtime)
    table.registerPlugin('realtime', DTableRealtime);
    @endif

    // Register built-in renderers
    Object.keys(DTableRenderers).forEach(name => {
        table.registerRenderer(name, DTableRenderers[name]);
    });
});
</script>
@endpush
