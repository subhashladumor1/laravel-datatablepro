/**
 * DTable Realtime Plugin
 * 
 * Enables realtime data updates via polling or websockets
 */

window.DTableRealtime = {
    name: 'realtime',
    
    init: function(dtable) {
        this.dtable = dtable;
        this.enabled = false;
        this.interval = null;
        this.websocket = null;
    },

    enable: function(config = {}) {
        if (this.enabled) return;
        
        this.config = {
            mode: 'polling', // 'polling' or 'websocket'
            interval: 5000,
            websocketUrl: null,
            ...config
        };
        
        this.enabled = true;
        
        if (this.config.mode === 'websocket' && this.config.websocketUrl) {
            this.enableWebSocket();
        } else {
            this.enablePolling();
        }
    },

    disable: function() {
        this.enabled = false;
        
        if (this.interval) {
            clearInterval(this.interval);
            this.interval = null;
        }
        
        if (this.websocket) {
            this.websocket.close();
            this.websocket = null;
        }
    },

    enablePolling: function() {
        this.interval = setInterval(() => {
            if (!this.dtable.state.loading) {
                this.dtable.loadData();
            }
        }, this.config.interval);
    },

    enableWebSocket: function() {
        this.websocket = new WebSocket(this.config.websocketUrl);
        
        this.websocket.onopen = () => {
            console.log('DTable: WebSocket connected');
            this.subscribe();
        };
        
        this.websocket.onmessage = (event) => {
            try {
                const data = JSON.parse(event.data);
                this.handleRealtimeUpdate(data);
            } catch (e) {
                console.error('DTable: Failed to parse WebSocket message', e);
            }
        };
        
        this.websocket.onerror = (error) => {
            console.error('DTable: WebSocket error', error);
        };
        
        this.websocket.onclose = () => {
            console.log('DTable: WebSocket disconnected');
            
            // Attempt reconnection
            if (this.enabled) {
                setTimeout(() => this.enableWebSocket(), 5000);
            }
        };
    },

    subscribe: function() {
        if (this.websocket && this.websocket.readyState === WebSocket.OPEN) {
            this.websocket.send(JSON.stringify({
                action: 'subscribe',
                channel: this.dtable.config.realtimeChannel || 'datatable'
            }));
        }
    },

    handleRealtimeUpdate: function(data) {
        if (data.type === 'refresh') {
            // Full refresh
            this.dtable.loadData();
        } else if (data.type === 'update') {
            // Partial update
            this.updateRow(data.row);
        } else if (data.type === 'delete') {
            // Delete row
            this.deleteRow(data.id);
        } else if (data.type === 'insert') {
            // Insert row
            this.insertRow(data.row);
        }
    },

    updateRow: function(row) {
        const index = this.dtable.state.data.findIndex(r => r.id === row.id);
        if (index !== -1) {
            this.dtable.state.data[index] = row;
            this.dtable.renderTable();
        }
    },

    deleteRow: function(id) {
        const index = this.dtable.state.data.findIndex(r => r.id === id);
        if (index !== -1) {
            this.dtable.state.data.splice(index, 1);
            this.dtable.state.recordsTotal--;
            this.dtable.state.recordsFiltered--;
            this.dtable.renderTable();
            this.dtable.renderPagination();
            this.dtable.renderInfo();
        }
    },

    insertRow: function(row) {
        this.dtable.state.data.unshift(row);
        this.dtable.state.recordsTotal++;
        this.dtable.state.recordsFiltered++;
        this.dtable.renderTable();
        this.dtable.renderPagination();
        this.dtable.renderInfo();
    }
};
