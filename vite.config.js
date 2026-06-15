import { defineConfig } from 'vite';
import path from 'node:path';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.ts'],
            refresh: [
                'resources/views/**',
                'routes/**',
                'modules/**/Resources/**',
                'modules/**/assets/**',
            ],
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        tailwindcss(),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
            '@css': path.resolve(__dirname, 'resources/css'),
            '@modules': path.resolve(__dirname, 'modules'),
            ziggy: path.resolve(__dirname, 'vendor/tightenco/ziggy'),
        },
    },
    build: {
        // Split vendor chunks supaya cache lebih awet:
        //   - vendor-vue: vue + inertia + ziggy (jarang berubah)
        //   - vendor-icons: lucide-vue-next (besar, jarang berubah)
        //   - vendor-i18n: vue-i18n + intl helpers
        // Sisanya masuk ke chunk app/page.
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (id.includes('node_modules')) {
                        if (id.includes('lucide-vue-next')) return 'vendor-icons';
                        if (id.includes('vue-i18n') || id.includes('@intlify')) return 'vendor-i18n';
                        if (
                            id.includes('@inertiajs') ||
                            id.includes('/vue/') ||
                            id.includes('ziggy')
                        )
                            return 'vendor-vue';
                    }
                    return undefined;
                },
            },
        },
    },
});
