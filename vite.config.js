import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from 'tailwindcss'
import flareSourcemapUploader from '@flareapp/vite-plugin-sourcemap-uploader';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                //resources/scss/style.scss',
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        flareSourcemapUploader.default({
            key: 'zUKxLuLu568IhI1XL3uJDprYdM74p2Wk'
        }),
    ],
    resolve: {
        alias: {
            vue: 'vue/dist/vue.esm-bundler.js',
        },
    },
    css: {
        postcss: {
            plugins: [tailwindcss],
        },
    },
});