import { defineConfig } from 'vite';
import path from 'path';

export default defineConfig({
    build: {
        outDir: 'src/Resources/dist',
        emptyOutDir: true,
        manifest: true,
        rollupOptions: {
            input: {
                'dtable': path.resolve(__dirname, 'src/Resources/assets/js/dtable.core.js'),
                'dtable-renderers': path.resolve(__dirname, 'src/Resources/assets/js/dtable.renderers.js'),
                'dtable-styles': path.resolve(__dirname, 'src/Resources/assets/scss/dtable.scss'),
            },
            output: {
                entryFileNames: 'js/[name].js',
                chunkFileNames: 'js/[name]-[hash].js',
                assetFileNames: (assetInfo) => {
                    if (assetInfo.name.endsWith('.css')) {
                        return 'css/[name][extname]';
                    }
                    return 'assets/[name]-[hash][extname]';
                },
            },
        },
    },
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'src/Resources/assets'),
        },
    },
});
