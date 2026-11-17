import { test, expect, testUser } from './helpers/auth.js';
import { creationOptions } from './fixtures/creationOptions.js';

test('an existing session is checked through the API with the bypass off', async ({
    page,
}) => {
    // /character/create loads the creation catalog as soon as it renders.
    await page.route(
        '**/api/rulesets/current/character-creation-options',
        (route) =>
            route.fulfill({
                status: 200,
                json: { options: creationOptions },
            }),
    );
    const response = page.waitForResponse('**/api/user');
    await page.goto('/character/create');
    expect((await response).status()).toBe(200);
    await expect(page.getByLabel('Nom du personnage')).toBeVisible();
    await expect(page.locator('.navbar-username')).toHaveText(testUser.name);
});

test.describe('without an authenticated session', () => {
    test.use({ authenticated: false });

    test('protected routes redirect to login', async ({ page }) => {
        await page.goto('/character/create');
        await expect(page).toHaveURL('/login');
        await expect(
            page.getByRole('button', { name: 'Connexion', exact: true }),
        ).toBeVisible();
        await expect(page.getByLabel('Nom du personnage')).toHaveCount(0);
    });

    test('login checks the session and logout protects routes again', async ({
        page,
    }) => {
        let authenticated = false;
        await page.route('**/api/user', (route) =>
            route.fulfill({
                status: authenticated ? 200 : 401,
                json: authenticated
                    ? testUser
                    : { message: 'Unauthenticated.' },
            }),
        );
        await page.route('**/sanctum/csrf-cookie', (route) =>
            route.fulfill({ status: 204 }),
        );
        await page.route('**/api/login', (route) => {
            expect(route.request().method()).toBe('POST');
            expect(route.request().postDataJSON()).toEqual({
                email: testUser.email,
                password: 'test-password',
            });
            authenticated = true;
            return route.fulfill({ status: 204 });
        });
        await page.route('**/api/logout', (route) => {
            authenticated = false;
            return route.fulfill({ status: 204 });
        });
        await page.goto('/login');
        await page.getByPlaceholder('Courriel').fill(testUser.email);
        await page
            .getByPlaceholder('Mot de passe', { exact: true })
            .fill('test-password');
        await page
            .getByRole('button', { name: 'Connexion', exact: true })
            .click();
        await expect(page).toHaveURL('/');
        await expect(page.locator('.navbar-username')).toHaveText(
            testUser.name,
        );
        await page.locator('.navbar-link', { hasText: 'Déconnexion' }).click();
        await expect(page).toHaveURL('/login');
        await page.goto('/character/create');
        await expect(page).toHaveURL('/login');
    });

    test('invalid credentials keep the user on the login page', async ({
        page,
    }) => {
        await page.route('**/sanctum/csrf-cookie', (route) =>
            route.fulfill({ status: 204 }),
        );
        await page.route('**/api/login', (route) =>
            route.fulfill({
                status: 422,
                json: { message: 'Identifiants invalides.' },
            }),
        );
        await page.goto('/login');
        await page.getByPlaceholder('Courriel').fill(testUser.email);
        await page
            .getByPlaceholder('Mot de passe', { exact: true })
            .fill('wrong-password');
        await page
            .getByRole('button', { name: 'Connexion', exact: true })
            .click();
        await expect(page.getByText('Identifiants invalides.')).toBeVisible();
        await expect(page).toHaveURL('/login');
    });

    const fillSignupForm = async (page, passwordConfirmation) => {
        await page.getByPlaceholder("Nom d'utilisateur").fill(testUser.name);
        await page.getByPlaceholder('Courriel').fill(testUser.email);
        await page
            .getByPlaceholder('Mot de passe', { exact: true })
            .fill('test-password');
        await page
            .getByPlaceholder('Confirmer le mot de passe')
            .fill(passwordConfirmation);
        await page.getByRole('button', { name: 'Créer', exact: true }).click();
    };

    test('signup registers the user and redirects home', async ({ page }) => {
        await page.route('**/sanctum/csrf-cookie', (route) =>
            route.fulfill({ status: 204 }),
        );
        await page.route('**/api/register', (route) => {
            expect(route.request().method()).toBe('POST');
            expect(route.request().postDataJSON()).toEqual({
                name: testUser.name,
                email: testUser.email,
                password: 'test-password',
                password_confirmation: 'test-password',
            });
            return route.fulfill({ status: 201, json: { user: testUser } });
        });
        await page.goto('/signup');
        await fillSignupForm(page, 'test-password');
        await expect(page).toHaveURL('/');
        await expect(page.locator('.navbar-username')).toHaveText(
            testUser.name,
        );
    });

    test('signup rejects mismatched passwords without calling the API', async ({
        page,
    }) => {
        let registerCalls = 0;
        await page.route('**/api/register', (route) => {
            registerCalls += 1;
            return route.fulfill({ status: 201, json: { user: testUser } });
        });
        await page.goto('/signup');
        await fillSignupForm(page, 'other-password');
        await expect(page.getByRole('alert')).toHaveText(
            'Les mots de passe ne correspondent pas',
        );
        await expect(page).toHaveURL('/signup');
        expect(registerCalls).toBe(0);
    });

    test('signup shows the API error and stays on the page', async ({
        page,
    }) => {
        await page.route('**/sanctum/csrf-cookie', (route) =>
            route.fulfill({ status: 204 }),
        );
        await page.route('**/api/register', (route) =>
            route.fulfill({
                status: 422,
                json: { message: 'Ce courriel est déjà utilisé.' },
            }),
        );
        await page.goto('/signup');
        await fillSignupForm(page, 'test-password');
        await expect(page.getByRole('alert')).toHaveText(
            'Ce courriel est déjà utilisé.',
        );
        await expect(page).toHaveURL('/signup');
    });
});
