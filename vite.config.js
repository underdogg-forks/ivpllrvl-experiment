import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/assets/core/css/style-tailwind.css',
                'resources/assets/invoiceplane/css/style-tailwind.css',
                'resources/assets/invoiceplane_blue/css/style-tailwind.css',
                'resources/assets/nord/css/nord.css',
                'resources/assets/orange/css/orange.css',
                'resources/assets/reddit/css/reddit.css',
                'resources/assets/overrides/filament-fixes.css',
                'resources/js/app.js'
            ],
            refresh: true
        }),
        tailwindcss()
    ]
});
