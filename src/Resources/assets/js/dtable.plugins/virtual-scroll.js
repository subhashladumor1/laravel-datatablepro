/**
 * DTable Virtual Scroll Plugin
 * 
 * Enables virtual scrolling for large datasets
 */

window.DTableVirtualScroll = {
    name: 'virtualScroll',
    
    init: function(dtable) {
        this.dtable = dtable;
        this.enabled = false;
        this.rowHeight = 40;
        this.visibleRows = 20;
        this.scrollTop = 0;
        this.totalHeight = 0;
    },

    enable: function() {
        if (this.enabled) return;
        
        this.enabled = true;
        this.setupVirtualScroll();
    },

    disable: function() {
        this.enabled = false;
        this.teardownVirtualScroll();
    },

    setupVirtualScroll: function() {
        const container = this.dtable.elements.tableContainer;
        
        // Set container height
        container.style.maxHeight = (this.rowHeight * this.visibleRows) + 'px';
        container.style.overflow = 'auto';
        container.style.position = 'relative';
        
        // Create scroll spacer
        this.scrollSpacer = document.createElement('div');
        this.scrollSpacer.style.position = 'absolute';
        this.scrollSpacer.style.top = '0';
        this.scrollSpacer.style.left = '0';
        this.scrollSpacer.style.width = '1px';
        this.scrollSpacer.style.pointerEvents = 'none';
        container.appendChild(this.scrollSpacer);
        
        // Attach scroll event
        this.handleScroll = this.onScroll.bind(this);
        container.addEventListener('scroll', this.handleScroll);
        
        // Override renderTable
        this.originalRenderTable = this.dtable.renderTable.bind(this.dtable);
        this.dtable.renderTable = this.renderVirtualTable.bind(this);
    },

    teardownVirtualScroll: function() {
        const container = this.dtable.elements.tableContainer;
        
        container.style.maxHeight = '';
        container.style.overflow = '';
        
        if (this.scrollSpacer) {
            this.scrollSpacer.remove();
        }
        
        container.removeEventListener('scroll', this.handleScroll);
        this.dtable.renderTable = this.originalRenderTable;
    },

    onScroll: function(e) {
        this.scrollTop = e.target.scrollTop;
        this.renderVirtualTable();
    },

    renderVirtualTable: function() {
        const data = this.dtable.state.data;
        const startIndex = Math.floor(this.scrollTop / this.rowHeight);
        const endIndex = Math.min(data.length, startIndex + this.visibleRows + 5);
        const visibleData = data.slice(startIndex, endIndex);
        
        // Update total height
        this.totalHeight = data.length * this.rowHeight;
        this.scrollSpacer.style.height = this.totalHeight + 'px';
        
        // Render visible rows with offset
        const rows = visibleData.map((row, index) => {
            const actualIndex = startIndex + index;
            const cells = this.dtable.config.columns.map((col) => {
                let value = row[col.key] || '';
                
                if (col.clientRenderer && this.dtable.renderers[col.clientRenderer]) {
                    value = this.dtable.renderers[col.clientRenderer](value, row, col);
                }
                
                return `<td data-label="${col.label}">${value}</td>`;
            }).join('');

            const offsetTop = actualIndex * this.rowHeight;
            return `<tr data-row="${actualIndex}" style="position: absolute; top: ${offsetTop}px; left: 0; right: 0;">${cells}</tr>`;
        }).join('');

        this.dtable.elements.tbody.style.position = 'relative';
        this.dtable.elements.tbody.style.height = this.totalHeight + 'px';
        this.dtable.elements.tbody.innerHTML = rows || '<tr><td colspan="' + this.dtable.config.columns.length + '" class="dtable-empty">No data available</td></tr>';
    }
};
