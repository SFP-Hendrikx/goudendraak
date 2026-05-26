import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/kassa/cashdesk.js',
            ],
            refresh: true,
        }),
        vue(),
    ],

    resolve: {
        alias: {
            vue: 'vue/dist/vue.esm-bundler.js',
        },
    },

    server: {
        cors: true,
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
