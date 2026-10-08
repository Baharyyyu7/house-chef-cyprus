import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            // Yönetim stilleri ayrı girdi: genel sayfaların paketine 25 KB
            // kullanılmayan CSS ekliyordu.
            input: ['resources/css/app.css', 'resources/css/admin-dashboard.css', 'resources/js/app.js'],
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
