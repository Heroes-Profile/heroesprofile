import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from 'tailwindcss'


export default defineConfig({
    //base: process.env.NODE_ENV === 'production' ? 'https://heroesprofile-website-rsfk4hfj3a-ue.a.run.app/' : '/',
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
