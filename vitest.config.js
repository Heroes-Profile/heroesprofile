import { defineConfig } from 'vitest/config';
import vue from '@vitejs/plugin-vue';

// Component tests (npm run test:js). The browser tests are Playwright's, under tests/browser.
export default defineConfig({
    plugins: [vue()],
    resolve: {
        alias: {
            vue: 'vue/dist/vue.esm-bundler.js',
        },
    },
    test: {
        environment: 'jsdom',
        include: ['tests/js/**/*.spec.js'],
        setupFiles: ['tests/js/setup.js'],
    },
});
