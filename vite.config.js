import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    build: {
        cssCodeSplit: true,
        chunkSizeWarningLimit: 500,
        rollupOptions: {
            output: {
                // rolldown nesne biçimini kabul etmiyor, işlev bekliyor.
                manualChunks(id) {
                    if (id.includes('node_modules/chart.js')) {
                        return 'chart';
                    }

                    return undefined;
                },
            },
        },
    },
    server: {
        hmr: { host: 'localhost' },
    },
});
