import { defineConfig, devices } from '@playwright/test';

const baseURL = process.env.APP_URL ?? 'http://127.0.0.1:8000';

export default defineConfig({
    testDir: './tests/e2e',
    fullyParallel: true,
    forbidOnly: !!process.env.CI,
    retries: process.env.CI ? 2 : 0,
    // One PHP server and a heavy catalogue page: with several workers a navigation can wait behind
    // the axe audit of another browser and time out. PW_WORKERS raises it on a roomy machine.
    workers: process.env.CI ? 1 : Number(process.env.PW_WORKERS ?? 1),
    reporter: process.env.CI ? [['html', { open: 'never' }], ['github']] : 'list',
    use: {
        baseURL,
        trace: 'on-first-retry',
        locale: 'es-ES',
    },
    projects: [
        { name: 'chromium', use: { ...devices['Desktop Chrome'] } },
        { name: 'mobile', use: { ...devices['Pixel 7'] } },
    ],
    // Starts the app before the tests unless something already answers at baseURL.
    // Playwright considers the server ready on 2xx, 3xx, 400, 401, 402 or 403.
    webServer: {
        command: 'php artisan serve --host=127.0.0.1 --port=8000',
        url: baseURL,
        reuseExistingServer: !process.env.CI,
        timeout: 120 * 1000,
        stdout: 'ignore',
        stderr: 'pipe',
    },
});
