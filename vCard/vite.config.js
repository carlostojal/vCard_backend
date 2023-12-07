import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import basicSsl from '@vitejs/plugin-basic-ssl'

const host = '127.0.0.1';
const port = '80';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        // 003 load the basicSsl plugin
        basicSsl()
    ],
    // 004 set the server section
    server: {
        // 005 enabling the HTTPS
        https: true,
        // 006 setting the proxy with Laravel as target (origin)
        proxy: {
            '^(?!(\/\@vite|\/resources|\/node_modules))': {
                target: `http://${host}:${port}`,
            }
        },
        host: '0.0.0.0',
        port: 5173,
        // 007 be sure that you have the Hot Module Replacement
        hmr: { host },
    }
});

