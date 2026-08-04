import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/core.css',
                'resources/css/pages.css',
                // Must stay last of the three: brutalist.css overrides tokens
                // that core.css re-declares at its own tail.
                'resources/css/brutalist.css',
                'resources/js/core.js',
                'resources/js/chrome.js',
                'resources/css/filament/admin/theme.css',
            ],
            refresh: true,
        }),
    ],
    server: {
        host: '127.0.0.1',   // force IPv4 — default binds [::1] (IPv6) which the browser can't load cross-origin
        port: 5173,
        strictPort: false,   // bump if 5173 busy; hot file tracks the real port
        cors: true,          // let the app origin (127.0.0.1:8000 / localhost:8000) load dev assets
        hmr: { host: '127.0.0.1' },
    },
});
