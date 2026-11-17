import { test as base, expect } from '@playwright/test';

export const testUser = { id: 1, name: 'Test', email: 'test@example.com' };

// Exercise AuthProvider normally while keeping frontend tests independent of Laravel.
export const test = base.extend({
    authenticated: [true, { option: true }],
    page: async ({ page, authenticated }, use) => {
        await page.route('**/api/user', (route) =>
            route.fulfill({
                status: authenticated ? 200 : 401,
                json: authenticated
                    ? testUser
                    : { message: 'Unauthenticated.' },
            }),
        );
        // Playwright's fixture callback, not a React hook — eslint-plugin-react-hooks
        // mistakes it for one because of the `use` name.
        // eslint-disable-next-line react-hooks/rules-of-hooks
        await use(page);
    },
});

export { expect };
