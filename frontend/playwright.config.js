import { defineConfig } from '@playwright/test';

export default defineConfig({
    testDir: './tests',
    use: { baseURL: 'http://127.0.0.1:4173', channel: 'msedge' },
    webServer: {
        command: 'npm run dev -- --host 127.0.0.1 --port 4173 --strictPort',
        url: 'http://127.0.0.1:4173',
        reuseExistingServer: false,
        // Every API call is answered by tests/helpers/test.js. Must stay a distinct origin from
        // the dev server's own (127.0.0.1:4173): reusing it would make page.route's glob pattern
        // also intercept Vite's own asset/module requests and break every test.
        env: { VITE_API_URL: 'http://api.test' },
    },
});
