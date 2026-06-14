import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        host: '0.0.0.0', // Allow external connections from your network
        port: 5173,
        hmr: {
            // Force Vite's hot-module replacement to use secure websockets
            protocol: 'wss',
            host: 'localhost',
        },
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
