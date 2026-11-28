import { defineConfig, devices } from '@playwright/test';

// Browser tests (npm run test:browser). CI serves the site against the seeded test database
// (.github/workflows/browser.yaml); BASE_URL points a local run at a server that does the same.
export default defineConfig({
    testDir: 'tests/browser',
    // Pages run inline global queries on first load; a page with many filters takes a while
    timeout: 10 * 60 * 1000,
    expect: { timeout: 15 * 1000 },
    fullyParallel: true,
    workers: process.env.CI ? 4 : 2,
    retries: 0,
    reporter: process.env.CI ? [['github'], ['list']] : 'list',
    use: {
        baseURL: process.env.BASE_URL || 'http://127.0.0.1:8000',
        viewport: { width: 1440, height: 900 },
        trace: 'off',
        screenshot: 'off',
        video: 'off',
    },
    projects: [
        { name: 'chromium', use: { ...devices['Desktop Chrome'], viewport: { width: 1440, height: 900 } } },
    ],
});
