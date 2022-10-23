import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    server: {
        host: true,
        // https: true,
        // origin: "https://c090-117-20-68-195.au.ngrok.io/",
        origin: "http://cycletime.test",
        hmr: {
            // host: 'c090-117-20-68-195.au.ngrok.io'
            host: 'cycletime.test'
        }
    },
    plugins: [
        laravel({
            input: 'resources/js/app.jsx',
            refresh: true,
        }),
        react(),
    ],
});
