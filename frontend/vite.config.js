import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const root = path.dirname(fileURLToPath(import.meta.url));

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            publicDirectory: path.resolve(root, '../backend/public'),
            refresh: [
                'resources/views/**',
                '../backend/routes/**',
                '../backend/app/Livewire/**',
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/backend/storage/framework/views/**'],
        },
    },
});
