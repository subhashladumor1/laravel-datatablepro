/**
 * DTable Core
 * 
 * High-performance vanilla JavaScript DataTable implementation
 * with responsive design, virtual scrolling, and realtime updates.
 */

class DTable {
    constructor(container, config) {
        this.container = typeof container === 'string' 
            ? document.querySelector(container) 
            : container;
        
        if (!this.container) {
            throw new Error('DTable container not found');
        }

        this.config = {
            ajax: '',
            columns: [],
            pageLength: 10,
            responsive: false,
            persistKey: null,
            exportUrl: null,
            filters: [],
            virtualScroll: false,
            realtime: false,
            realtimeInterval: 5000,
            debounceDelay: 300,
            responsiveBreakpoint: 768,
            ...config
        };

        this.state = {
            draw: 1,
            start: 0,
            length: this.config.pageLength,
            search: '',
            order: [],
            filters: {},
            recordsTotal: 0,
            recordsFiltered: 0,
            data: [],
            loading: false
        };

        this.debounceTimers = {};
        this.realtimeInterval = null;
        this.renderers = {};
        this.plugins = {};

        this.init();
    }

    static init(container, config) {
        return new DTable(container, config);
    }

    init() {
        this.buildUI();
        this.attachEvents();
        this.loadPersistedState();
        this.loadData();
        
        if (this.config.realtime) {
            this.enableRealtime();
        }

        if (this.config.virtualScroll) {
            this.enableVirtualScroll();
        }
    }

    buildUI() {
        this.container.innerHTML = `
            <div class="dtable-wrapper">
                <div class="dtable-toolbar">
                    <div class="dtable-toolbar-left">
                        ${this.buildFiltersHTML()}
                    </div>
                    <div class="dtable-toolbar-right">
                        <div class="dtable-search">
                            <input type="text" class="dtable-search-input" placeholder="Search...">
                        </div>
                        ${this.buildExportButtonsHTML()}
                    </div>
                </div>
                <div class="dtable-table-container">
                    <table class="dtable-table">
                        <thead>
                            <tr>
                                ${this.buildHeaderHTML()}
                            </tr>
                        </thead>
                        <tbody class="dtable-tbody">
                            ${this.buildSkeletonHTML()}
                        </tbody>
                    </table>
                </div>
                <div class="dtable-footer">
                    <div class="dtable-info"></div>
                    <div class="dtable-pagination"></div>
                </div>
            </div>
        `;

        this.elements = {
            wrapper: this.container.querySelector('.dtable-wrapper'),
            toolbar: this.container.querySelector('.dtable-toolbar'),
            searchInput: this.container.querySelector('.dtable-search-input'),
            tableContainer: this.container.querySelector('.dtable-table-container'),
            table: this.container.querySelector('.dtable-table'),
            tbody: this.container.querySelector('.dtable-tbody'),
            info: this.container.querySelector('.dtable-info'),
            pagination: this.container.querySelector('.dtable-pagination')
        };
    }

    buildHeaderHTML() {
        return this.config.columns.map((col, index) => `
            <th data-column="${index}" data-orderable="${col.orderable || false}">
                ${col.label}
                ${col.orderable ? '<span class="dtable-sort-icon"></span>' : ''}
            </th>
        `).join('');
    }

    buildFiltersHTML() {
        if (!this.config.filters || this.config.filters.length === 0) {
            return '';
        }

        return `
            <div class="dtable-filters">
                ${this.config.filters.map(filter => {
                    switch (filter.type) {
                        case 'text':
                            return `
                                <input type="text" 
                                    class="dtable-filter" 
                                    data-filter="${filter.key}" 
                                    placeholder="${filter.label}">
                            `;
                        case 'select':
                            return `
                                <select class="dtable-filter" data-filter="${filter.key}">
                                    <option value="">${filter.label}</option>
                                    ${Object.entries(filter.options || {}).map(([value, label]) => 
                                        `<option value="${value}">${label}</option>`
                                    ).join('')}
                                </select>
                            `;
                        case 'date-range':
                            return `
                                <input type="date" 
                                    class="dtable-filter" 
                                    data-filter="${filter.key}[from]" 
                                    placeholder="${filter.label} From">
                                <input type="date" 
                                    class="dtable-filter" 
                                    data-filter="${filter.key}[to]" 
                                    placeholder="${filter.label} To">
                            `;
                        default:
                            return '';
                    }
                }).join('')}
            </div>
        `;
    }

    buildExportButtonsHTML() {
        if (!this.config.exportUrl) {
            return '';
        }

        return `
            <div class="dtable-export">
                <button class="dtable-export-btn" data-format="csv">CSV</button>
                <button class="dtable-export-btn" data-format="xlsx">XLSX</button>
                <button class="dtable-export-btn" data-format="pdf">PDF</button>
                <button class="dtable-export-btn" data-format="image">Image</button>
            </div>
        `;
    }

    buildSkeletonHTML() {
        return Array(5).fill(0).map(() => `
            <tr class="dtable-skeleton">
                ${this.config.columns.map(() => 
                    '<td><div class="dtable-skeleton-cell"></div></td>'
                ).join('')}
            </tr>
        `).join('');
    }

    attachEvents() {
        // Search
        this.elements.searchInput.addEventListener('input', (e) => {
            this.debounce('search', () => {
                this.state.search = e.target.value;
                this.state.start = 0;
                this.loadData();
                this.updateHistory();
            });
        });

        // Sorting
        this.elements.table.querySelector('thead').addEventListener('click', (e) => {
            const th = e.target.closest('th');
            if (!th || th.dataset.orderable !== 'true') return;

            const columnIndex = parseInt(th.dataset.column);
            const currentOrder = this.state.order.find(o => o.column === columnIndex);
            
            if (e.shiftKey) {
                // Multi-column sort
                if (currentOrder) {
                    currentOrder.dir = currentOrder.dir === 'asc' ? 'desc' : 'asc';
                } else {
                    this.state.order.push({ column: columnIndex, dir: 'asc' });
                }
            } else {
                // Single column sort
                if (currentOrder && this.state.order.length === 1) {
                    currentOrder.dir = currentOrder.dir === 'asc' ? 'desc' : 'asc';
                } else {
                    this.state.order = [{ column: columnIndex, dir: 'asc' }];
                }
            }

            this.state.start = 0;
            this.updateSortIcons();
            this.loadData();
            this.updateHistory();
        });

        // Filters
        const filterInputs = this.container.querySelectorAll('.dtable-filter');
        filterInputs.forEach(input => {
            input.addEventListener('change', () => this.handleFilterChange());
        });

        // Export buttons
        const exportButtons = this.container.querySelectorAll('.dtable-export-btn');
        exportButtons.forEach(btn => {
            btn.addEventListener('click', () => this.export(btn.dataset.format));
        });

        // Responsive
        if (this.config.responsive) {
            window.addEventListener('resize', () => this.handleResize());
            this.handleResize();
        }
    }

    debounce(key, callback) {
        clearTimeout(this.debounceTimers[key]);
        this.debounceTimers[key] = setTimeout(callback, this.config.debounceDelay);
    }

    handleFilterChange() {
        this.state.filters = {};
        const filterInputs = this.container.querySelectorAll('.dtable-filter');
        
        filterInputs.forEach(input => {
            const filterKey = input.dataset.filter;
            const value = input.value;
            
            if (value) {
                // Handle nested keys like date-range[from]
                if (filterKey.includes('[')) {
                    const [key, subKey] = filterKey.match(/([^\[]+)\[([^\]]+)\]/).slice(1);
                    if (!this.state.filters[key]) {
                        this.state.filters[key] = {};
                    }
                    this.state.filters[key][subKey] = value;
                } else {
                    this.state.filters[filterKey] = value;
                }
            }
        });

        this.state.start = 0;
        this.loadData();
        this.updateHistory();
    }

    async loadData() {
        if (this.state.loading) return;

        this.state.loading = true;
        this.showLoading();

        try {
            const response = await fetch(this.config.ajax, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({
                    draw: this.state.draw++,
                    start: this.state.start,
                    length: this.state.length,
                    search: { value: this.state.search },
                    order: this.state.order,
                    filters: this.state.filters
                })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            this.state.data = data.data;
            this.state.recordsTotal = data.recordsTotal;
            this.state.recordsFiltered = data.recordsFiltered;

            this.renderTable();
            this.renderPagination();
            this.renderInfo();
            this.persistState();
        } catch (error) {
            console.error('DTable: Failed to load data', error);
            this.showError(error.message);
        } finally {
            this.state.loading = false;
        }
    }

    renderTable() {
        const rows = this.state.data.map((row, rowIndex) => {
            const cells = this.config.columns.map((col, colIndex) => {
                let value = row[col.key] || '';
                
                // Apply client renderer if specified
                if (col.clientRenderer && this.renderers[col.clientRenderer]) {
                    value = this.renderers[col.clientRenderer](value, row, col);
                }
                
                return `<td data-label="${col.label}">${value}</td>`;
            }).join('');

            return `<tr data-row="${rowIndex}">${cells}</tr>`;
        }).join('');

        this.elements.tbody.innerHTML = rows || '<tr><td colspan="' + this.config.columns.length + '" class="dtable-empty">No data available</td></tr>';
    }

    renderPagination() {
        const totalPages = Math.ceil(this.state.recordsFiltered / this.state.length);
        const currentPage = Math.floor(this.state.start / this.state.length) + 1;

        if (totalPages <= 1) {
            this.elements.pagination.innerHTML = '';
            return;
        }

        let html = '<div class="dtable-pagination-buttons">';
        
        // Previous button
        html += `<button class="dtable-page-btn" data-page="prev" ${currentPage === 1 ? 'disabled' : ''}>Previous</button>`;
        
        // Page numbers
        const startPage = Math.max(1, currentPage - 2);
        const endPage = Math.min(totalPages, currentPage + 2);
        
        if (startPage > 1) {
            html += `<button class="dtable-page-btn" data-page="1">1</button>`;
            if (startPage > 2) html += '<span>...</span>';
        }
        
        for (let i = startPage; i <= endPage; i++) {
            html += `<button class="dtable-page-btn ${i === currentPage ? 'active' : ''}" data-page="${i}">${i}</button>`;
        }
        
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) html += '<span>...</span>';
            html += `<button class="dtable-page-btn" data-page="${totalPages}">${totalPages}</button>`;
        }
        
        // Next button
        html += `<button class="dtable-page-btn" data-page="next" ${currentPage === totalPages ? 'disabled' : ''}>Next</button>`;
        
        html += '</div>';
        this.elements.pagination.innerHTML = html;

        // Attach pagination events
        this.elements.pagination.querySelectorAll('.dtable-page-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const page = btn.dataset.page;
                if (page === 'prev') {
                    this.state.start = Math.max(0, this.state.start - this.state.length);
                } else if (page === 'next') {
                    this.state.start = Math.min(
                        (totalPages - 1) * this.state.length,
                        this.state.start + this.state.length
                    );
                } else {
                    this.state.start = (parseInt(page) - 1) * this.state.length;
                }
                this.loadData();
                this.updateHistory();
            });
        });
    }

    renderInfo() {
        const start = this.state.start + 1;
        const end = Math.min(this.state.start + this.state.length, this.state.recordsFiltered);
        const total = this.state.recordsFiltered;
        
        this.elements.info.textContent = `Showing ${start} to ${end} of ${total} entries`;
    }

    updateSortIcons() {
        const headers = this.elements.table.querySelectorAll('th');
        headers.forEach(th => {
            const icon = th.querySelector('.dtable-sort-icon');
            if (!icon) return;

            const columnIndex = parseInt(th.dataset.column);
            const order = this.state.order.find(o => o.column === columnIndex);
            
            icon.className = 'dtable-sort-icon';
            if (order) {
                icon.classList.add(order.dir === 'asc' ? 'asc' : 'desc');
            }
        });
    }

    showLoading() {
        this.elements.tbody.innerHTML = this.buildSkeletonHTML();
    }

    showError(message) {
        this.elements.tbody.innerHTML = `
            <tr><td colspan="${this.config.columns.length}" class="dtable-error">
                Error loading data: ${message}
            </td></tr>
        `;
    }

    async export(format) {
        if (!this.config.exportUrl) return;

        const url = `${this.config.exportUrl}?format=${format}`;
        
        if (format === 'image') {
            // Client-side image export using html2canvas
            if (window.DTableImageExport) {
                window.DTableImageExport.export(this.elements.table);
            } else {
                alert('Image export plugin not loaded');
            }
            return;
        }

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({
                    search: { value: this.state.search },
                    filters: this.state.filters
                })
            });

            if (!response.ok) {
                throw new Error('Export failed');
            }

            // Check if it's a queued export (JSON response) or immediate download
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                const data = await response.json();
                if (data.download_url) {
                    alert('Export queued. Download URL: ' + data.download_url);
                    window.open(data.download_url, '_blank');
                }
            } else {
                // Immediate download
                const blob = await response.blob();
                const downloadUrl = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = downloadUrl;
                a.download = `export-${Date.now()}.${format}`;
                document.body.appendChild(a);
                a.click();
                a.remove();
                window.URL.revokeObjectURL(downloadUrl);
            }
        } catch (error) {
            console.error('Export failed:', error);
            alert('Export failed: ' + error.message);
        }
    }

    registerRenderer(name, callback) {
        this.renderers[name] = callback;
    }

    registerPlugin(name, plugin) {
        this.plugins[name] = plugin;
        if (plugin.init) {
            plugin.init(this);
        }
    }

    enableRealtime() {
        this.realtimeInterval = setInterval(() => {
            this.loadData();
        }, this.config.realtimeInterval);
    }

    disableRealtime() {
        if (this.realtimeInterval) {
            clearInterval(this.realtimeInterval);
            this.realtimeInterval = null;
        }
    }

    enableVirtualScroll() {
        if (this.plugins.virtualScroll) {
            this.plugins.virtualScroll.enable();
        }
    }

    handleResize() {
        const isMobile = window.innerWidth < this.config.responsiveBreakpoint;
        this.elements.wrapper.classList.toggle('dtable-mobile', isMobile);
    }

    persistState() {
        if (!this.config.persistKey) return;

        const state = {
            search: this.state.search,
            order: this.state.order,
            filters: this.state.filters,
            pageLength: this.state.length
        };

        localStorage.setItem(`dtable_${this.config.persistKey}`, JSON.stringify(state));
    }

    loadPersistedState() {
        if (!this.config.persistKey) return;

        const saved = localStorage.getItem(`dtable_${this.config.persistKey}`);
        if (saved) {
            try {
                const state = JSON.parse(saved);
                this.state.search = state.search || '';
                this.state.order = state.order || [];
                this.state.filters = state.filters || {};
                this.state.length = state.pageLength || this.config.pageLength;
            } catch (e) {
                console.error('Failed to load persisted state:', e);
            }
        }
    }

    updateHistory() {
        const params = new URLSearchParams(window.location.search);
        params.set('page', Math.floor(this.state.start / this.state.length) + 1);
        if (this.state.search) params.set('search', this.state.search);
        
        const newUrl = `${window.location.pathname}?${params.toString()}`;
        history.pushState({}, '', newUrl);
    }

    destroy() {
        this.disableRealtime();
        this.container.innerHTML = '';
    }
}

// Export to global scope
window.DTable = DTable;
