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
});
