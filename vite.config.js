import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    // لو التطبيق شغال من مسار فرعي ضع في .env: VITE_BASE_PATH=/public/
    base: process.env.VITE_BASE_PATH || '/',
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
});
