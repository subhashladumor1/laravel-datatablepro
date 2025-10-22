/**
 * DTable Image Export
 * 
 * Client-side image export using html2canvas
 */

window.DTableImageExport = {
    async export(table, options = {}) {
        // Check if html2canvas is loaded
        if (typeof html2canvas === 'undefined') {
            console.error('html2canvas is not loaded. Please include it in your page.');
            alert('Image export requires html2canvas library');
            return;
        }

        const config = {
            filename: 'table-export-' + Date.now() + '.png',
            backgroundColor: '#ffffff',
            scale: 2,
            ...options
        };

        try {
            // Show loading indicator
            const loadingOverlay = this.createLoadingOverlay();
            document.body.appendChild(loadingOverlay);

            // Clone table for export (to avoid affecting original)
            const tableClone = table.cloneNode(true);
            tableClone.style.position = 'absolute';
            tableClone.style.left = '-9999px';
            tableClone.style.backgroundColor = config.backgroundColor;
            document.body.appendChild(tableClone);

            // Remove skeleton rows and loading indicators
            const skeletonRows = tableClone.querySelectorAll('.dtable-skeleton');
            skeletonRows.forEach(row => row.remove());

            // Generate canvas from table
            const canvas = await html2canvas(tableClone, {
                backgroundColor: config.backgroundColor,
                scale: config.scale,
                logging: false,
                useCORS: true
            });

            // Remove clone
            tableClone.remove();
            loadingOverlay.remove();

            // Convert canvas to blob
            canvas.toBlob((blob) => {
                // Download the image
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = config.filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                URL.revokeObjectURL(url);
            }, 'image/png');

        } catch (error) {
            console.error('Image export failed:', error);
            alert('Failed to export image: ' + error.message);
            
            // Cleanup
            const loadingOverlay = document.querySelector('.dtable-export-loading');
            if (loadingOverlay) {
                loadingOverlay.remove();
            }
        }
    },

    createLoadingOverlay() {
        const overlay = document.createElement('div');
        overlay.className = 'dtable-export-loading';
        overlay.innerHTML = `
            <div class="dtable-export-spinner">
                <div class="spinner"></div>
                <p>Generating image...</p>
            </div>
        `;
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        `;
        
        const spinner = overlay.querySelector('.dtable-export-spinner');
        spinner.style.cssText = `
            background: white;
            padding: 30px;
            border-radius: 8px;
            text-align: center;
        `;
        
        return overlay;
    }
};
