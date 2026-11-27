import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from 'tailwindcss'
import flareSourcemapUploader from '@flareapp/vite';
import Components from 'unplugin-vue-components/vite';

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
        // Adds the imports for the component tags in each .vue file, so every page's chunk holds its own children.
        // Not resources/old: the old design has its own copies of the same names and registers them globally.
        Components({
            dirs: ['resources/js/components'],
            deep: true,
            directoryAsNamespace: false,
            directives: false,
            dts: false,
            exclude: [/[\\/]node_modules[\\/]/, /[\\/]resources[\\/]old[\\/]/],
        }),
        flareSourcemapUploader({
            key: 'zUKxLuLu568IhI1XL3uJDprYdM74p2Wk'
        }),
    ],
    resolve: {
        alias: [
            { find: 'vue', replacement: 'vue/dist/vue.esm-bundler.js' },
            // Zone data for 1970-2030 only (134 kB, not 732 kB); every date the site shows is 2014 or later
            { find: /^moment-timezone$/, replacement: 'moment-timezone/builds/moment-timezone-with-data-1970-2030.min.js' },
        ],
    },
    css: {
        postcss: {
            plugins: [tailwindcss],
        },
    },
    server: {
        hmr: {
            host: 'localhost',
        },
        // Nothing the frontend imports; the tests rewrite storage's compiled views on every run
        watch: {
            ignored: ['**/vendor/**', '**/storage/**', '**/tests/**', '**/docker-compose/**', '**/public/build/**'],
        },
    }
});
