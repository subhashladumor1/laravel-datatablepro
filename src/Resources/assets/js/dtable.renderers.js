/**
 * DTable Renderers
 * 
 * Built-in cell renderers and API for custom renderers
 */

window.DTableRenderers = {
    /**
     * Link renderer - renders a clickable link
     */
    link: function(value, row, column) {
        const url = column.attributes?.url || '#';
        const target = column.attributes?.target || '_self';
        const urlTemplate = url.replace(/\{(\w+)\}/g, (match, key) => row[key] || '');
        
        return `<a href="${urlTemplate}" target="${target}">${value}</a>`;
    },

    /**
     * Avatar renderer - renders user avatar
     */
    avatar: function(value, row, column) {
        const name = value || 'User';
        const imageUrl = column.attributes?.imageKey ? row[column.attributes.imageKey] : null;
        const size = column.attributes?.size || 32;
        
        if (imageUrl) {
            return `
                <div class="dtable-avatar">
                    <img src="${imageUrl}" alt="${name}" width="${size}" height="${size}">
                    <span>${name}</span>
                </div>
            `;
        }
        
        // Generate initials
        const initials = name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
        const colors = ['#FF6B6B', '#4ECDC4', '#45B7D1', '#FFA07A', '#98D8C8', '#F7DC6F', '#BB8FCE', '#85C1E9'];
        const colorIndex = name.charCodeAt(0) % colors.length;
        
        return `
            <div class="dtable-avatar">
                <div class="dtable-avatar-circle" style="background: ${colors[colorIndex]}; width: ${size}px; height: ${size}px;">
                    ${initials}
                </div>
                <span>${name}</span>
            </div>
        `;
    },

    /**
     * Badge renderer - renders a colored badge
     */
    badge: function(value, row, column) {
        const colorMap = column.attributes?.colorMap || {};
        const defaultColor = column.attributes?.defaultColor || 'secondary';
        const color = colorMap[value] || defaultColor;
        
        return `<span class="badge badge-${color}">${value}</span>`;
    },

    /**
     * Status renderer - renders status with icon
     */
    status: function(value, row, column) {
        const statusMap = {
            active: { icon: '✓', color: 'success', label: 'Active' },
            inactive: { icon: '✗', color: 'danger', label: 'Inactive' },
            pending: { icon: '⏱', color: 'warning', label: 'Pending' },
            ...column.attributes?.statusMap || {}
        };
        
        const status = statusMap[value] || { icon: '?', color: 'secondary', label: value };
        
        return `
            <span class="dtable-status badge-${status.color}">
                <span class="dtable-status-icon">${status.icon}</span>
                ${status.label}
            </span>
        `;
    },

    /**
     * Date renderer - formats date
     */
    date: function(value, row, column) {
        if (!value) return '';
        
        const format = column.attributes?.format || 'Y-m-d H:i:s';
        const date = new Date(value);
        
        if (isNaN(date.getTime())) return value;
        
        // Simple date formatting
        const pad = (n) => n.toString().padStart(2, '0');
        
        return format
            .replace('Y', date.getFullYear())
            .replace('m', pad(date.getMonth() + 1))
            .replace('d', pad(date.getDate()))
            .replace('H', pad(date.getHours()))
            .replace('i', pad(date.getMinutes()))
            .replace('s', pad(date.getSeconds()));
    },

    /**
     * DateTime renderer - formats datetime with relative time
     */
    datetime: function(value, row, column) {
        if (!value) return '';
        
        const date = new Date(value);
        if (isNaN(date.getTime())) return value;
        
        const now = new Date();
        const diff = now - date;
        const seconds = Math.floor(diff / 1000);
        const minutes = Math.floor(seconds / 60);
        const hours = Math.floor(minutes / 60);
        const days = Math.floor(hours / 24);
        
        let relative = '';
        if (days > 7) {
            relative = date.toLocaleDateString();
        } else if (days > 0) {
            relative = `${days} day${days > 1 ? 's' : ''} ago`;
        } else if (hours > 0) {
            relative = `${hours} hour${hours > 1 ? 's' : ''} ago`;
        } else if (minutes > 0) {
            relative = `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
        } else {
            relative = 'Just now';
        }
        
        return `<span title="${date.toLocaleString()}">${relative}</span>`;
    },

    /**
     * Currency renderer - formats currency
     */
    currency: function(value, row, column) {
        if (value === null || value === undefined || value === '') return '';
        
        const currency = column.attributes?.currency || 'USD';
        const decimals = column.attributes?.decimals || 2;
        const symbol = column.attributes?.symbol || '$';
        const position = column.attributes?.position || 'before';
        
        const formatted = parseFloat(value).toFixed(decimals).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        
        return position === 'before' 
            ? `${symbol}${formatted}` 
            : `${formatted} ${symbol}`;
    },

    /**
     * Number renderer - formats number
     */
    number: function(value, row, column) {
        if (value === null || value === undefined || value === '') return '';
        
        const decimals = column.attributes?.decimals || 0;
        return parseFloat(value).toFixed(decimals).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    },

    /**
     * Percentage renderer - formats percentage
     */
    percentage: function(value, row, column) {
        if (value === null || value === undefined || value === '') return '';
        
        const decimals = column.attributes?.decimals || 2;
        return `${parseFloat(value).toFixed(decimals)}%`;
    },

    /**
     * Boolean renderer - renders checkbox or icon
     */
    boolean: function(value, row, column) {
        const style = column.attributes?.style || 'icon';
        const truthy = [true, 1, '1', 'true', 'yes'];
        const isTrue = truthy.includes(value);
        
        if (style === 'checkbox') {
            return `<input type="checkbox" ${isTrue ? 'checked' : ''} disabled>`;
        }
        
        return isTrue 
            ? '<span class="dtable-bool-true">✓</span>' 
            : '<span class="dtable-bool-false">✗</span>';
    },

    /**
     * Image renderer - renders image thumbnail
     */
    image: function(value, row, column) {
        if (!value) return '';
        
        const width = column.attributes?.width || 50;
        const height = column.attributes?.height || 50;
        const lightbox = column.attributes?.lightbox || false;
        
        const img = `<img src="${value}" alt="Image" width="${width}" height="${height}" class="dtable-image">`;
        
        return lightbox 
            ? `<a href="${value}" class="dtable-image-lightbox" target="_blank">${img}</a>` 
            : img;
    },

    /**
     * Progress renderer - renders progress bar
     */
    progress: function(value, row, column) {
        if (value === null || value === undefined) return '';
        
        const percent = Math.min(100, Math.max(0, parseFloat(value)));
        const color = column.attributes?.color || 'primary';
        const showLabel = column.attributes?.showLabel !== false;
        
        return `
            <div class="dtable-progress">
                <div class="dtable-progress-bar" style="width: ${percent}%;" class="bg-${color}"></div>
                ${showLabel ? `<span class="dtable-progress-label">${percent}%</span>` : ''}
            </div>
        `;
    },

    /**
     * Actions renderer - renders action buttons
     */
    actions: function(value, row, column) {
        const actions = column.attributes?.actions || [];
        
        return actions.map(action => {
            const url = action.url.replace(/\{(\w+)\}/g, (match, key) => row[key] || '');
            const icon = action.icon || '';
            const label = action.label || '';
            const className = action.className || 'btn btn-sm';
            
            return `<a href="${url}" class="${className}" title="${label}">${icon} ${label}</a>`;
        }).join(' ');
    },

    /**
     * Truncate renderer - truncates long text
     */
    truncate: function(value, row, column) {
        if (!value) return '';
        
        const length = column.attributes?.length || 50;
        const suffix = column.attributes?.suffix || '...';
        
        if (value.length <= length) return value;
        
        return `<span title="${value}">${value.substring(0, length)}${suffix}</span>`;
    },

    /**
     * JSON renderer - renders formatted JSON
     */
    json: function(value, row, column) {
        if (!value) return '';
        
        try {
            const parsed = typeof value === 'string' ? JSON.parse(value) : value;
            return `<pre class="dtable-json">${JSON.stringify(parsed, null, 2)}</pre>`;
        } catch (e) {
            return value;
        }
    },

    /**
     * Tags renderer - renders multiple tags
     */
    tags: function(value, row, column) {
        if (!value) return '';
        
        const tags = Array.isArray(value) ? value : value.split(',');
        const color = column.attributes?.color || 'primary';
        
        return tags.map(tag => 
            `<span class="badge badge-${color} mr-1">${tag.trim()}</span>`
        ).join('');
    },

    /**
     * Custom renderer - allows inline function
     */
    custom: function(value, row, column) {
        if (column.attributes?.render && typeof column.attributes.render === 'function') {
            return column.attributes.render(value, row, column);
        }
        return value;
    }
};

// Auto-register all renderers with DTable
if (window.DTable) {
    Object.keys(window.DTableRenderers).forEach(name => {
        window.DTable.prototype.renderers[name] = window.DTableRenderers[name];
    });
}
