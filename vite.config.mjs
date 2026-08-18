import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/passkeys.js',
                'resources/js/qrcode-display.js',
            ],
            refresh: false,
        }),
    ],
});
