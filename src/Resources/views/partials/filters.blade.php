<div class="dtable-filters-container">
    @if($filters && count($filters) > 0)
        <form class="dtable-filters-form" method="GET">
            @foreach($filters as $filter)
                <div class="dtable-filter-group">
                    <label for="filter-{{ $filter['key'] }}" class="dtable-filter-label">
                        {{ $filter['label'] }}
                    </label>

                    @if($filter['type'] === 'text')
                        <input 
                            type="text" 
                            id="filter-{{ $filter['key'] }}" 
                            name="filters[{{ $filter['key'] }}]" 
                            class="dtable-filter-input"
                            value="{{ request()->input('filters.' . $filter['key']) }}"
                            placeholder="{{ $filter['label'] }}"
                        >

                    @elseif($filter['type'] === 'select')
                        <select 
                            id="filter-{{ $filter['key'] }}" 
                            name="filters[{{ $filter['key'] }}]" 
                            class="dtable-filter-select"
                        >
                            <option value="">All</option>
                            @foreach($filter['options'] ?? [] as $value => $label)
                                <option 
                                    value="{{ $value }}"
                                    {{ request()->input('filters.' . $filter['key']) == $value ? 'selected' : '' }}
                                >
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>

                    @elseif($filter['type'] === 'date-range')
                        <div class="dtable-date-range">
                            <input 
                                type="date" 
                                id="filter-{{ $filter['key'] }}-from" 
                                name="filters[{{ $filter['key'] }}][from]" 
                                class="dtable-filter-input"
                                value="{{ request()->input('filters.' . $filter['key'] . '.from') }}"
                                placeholder="From"
                            >
                            <span class="dtable-date-separator">to</span>
                            <input 
                                type="date" 
                                id="filter-{{ $filter['key'] }}-to" 
                                name="filters[{{ $filter['key'] }}][to]" 
                                class="dtable-filter-input"
                                value="{{ request()->input('filters.' . $filter['key'] . '.to') }}"
                                placeholder="To"
                            >
                        </div>

                    @elseif($filter['type'] === 'numeric-range')
                        <div class="dtable-numeric-range">
                            <input 
                                type="number" 
                                id="filter-{{ $filter['key'] }}-min" 
                                name="filters[{{ $filter['key'] }}][min]" 
                                class="dtable-filter-input"
                                value="{{ request()->input('filters.' . $filter['key'] . '.min') }}"
                                placeholder="Min"
                            >
                            <span class="dtable-range-separator">-</span>
                            <input 
                                type="number" 
                                id="filter-{{ $filter['key'] }}-max" 
                                name="filters[{{ $filter['key'] }}][max]" 
                                class="dtable-filter-input"
                                value="{{ request()->input('filters.' . $filter['key'] . '.max') }}"
                                placeholder="Max"
                            >
                        </div>
                    @endif
                </div>
            @endforeach

            <div class="dtable-filter-actions">
                <button type="submit" class="dtable-filter-btn dtable-filter-apply">
                    Apply Filters
                </button>
                <button type="button" class="dtable-filter-btn dtable-filter-clear" onclick="this.form.reset(); this.form.submit();">
                    Clear
                </button>
            </div>
        </form>
    @endif
</div>
