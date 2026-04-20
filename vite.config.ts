import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';
import Components from 'unplugin-vue-components/vite';
import { PrimeVueResolver } from '@primevue/auto-import-resolver';
import path from 'node:path';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/sass/styles.scss', 'resources/js/app.ts'],
            refresh: true,
        }),
        vue(),
        tailwindcss(),
        Components({
            resolvers: [PrimeVueResolver()],
            dts: 'resources/js/components.d.ts',
        }),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
        },
    },
    server: {
        host: '0.0.0.0',
        port: 5173,
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
