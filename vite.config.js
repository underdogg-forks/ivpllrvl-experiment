import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
            'resources/assets/nord/css/style-tailwind.css',
            'resources/assets/core/css/style-tailwind.css',
            'resources/assets/invoiceplane_blue/css/style-tailwind.css'
            'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
