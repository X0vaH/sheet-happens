import { test, expect, testUser } from './helpers/auth.js';

const viewports = [
    { width: 360, height: 800 },
    { width: 390, height: 844 },
    { width: 430, height: 932 },
];

async function expectNoHorizontalScroll(page) {
    const overflows = await page.evaluate(
        () =>
            document.documentElement.scrollWidth >
            document.documentElement.clientWidth,
    );
    expect(overflows).toBe(false);
}

test('account settings shows the signed-in account', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 800 });
    await page.goto('/settings');
    await expect(
        page.getByRole('heading', { name: 'Informations du compte' }),
    ).toBeVisible();
    await expect(
        page.getByText(`Adresse courriel : ${testUser.email}`),
    ).toBeVisible();
    await expect(
        page.getByText(`Nom d'utilisateur : ${testUser.name}`),
    ).toBeVisible();
    await expect(page.getByText('Mot de passe : **********')).toBeVisible();
    const direction = await page
        .locator('.settings-field', { hasText: "Nom d'utilisateur" })
        .evaluate((element) => getComputedStyle(element).flexDirection);
    expect(direction).toBe('row');
});

test('account settings navigates to the username and password forms', async ({
    page,
}) => {
    await page.goto('/settings');
    await page
        .locator('.settings-field', { hasText: "Nom d'utilisateur" })
        .getByRole('button', { name: 'Modifier' })
        .click();
    await expect(page).toHaveURL('/settings/username');
    await expect(
        page.getByRole('heading', {
            name: "Modification du nom d'utilisateur",
        }),
    ).toBeVisible();
    await expect(page.getByPlaceholder("Nom d'utilisateur")).toHaveValue(
        testUser.name,
    );
    await page.getByRole('button', { name: 'Retour' }).click();
    await expect(page).toHaveURL('/settings');

    await page.goto('/settings');
    await page
        .locator('.settings-field', { hasText: 'Mot de passe' })
        .getByRole('button', { name: 'Modifier' })
        .click();
    await expect(page).toHaveURL('/settings/password');
    await expect(
        page.getByRole('heading', { name: 'Modification du mot de passe' }),
    ).toBeVisible();
    await page.getByRole('button', { name: 'Retour' }).click();
    await expect(page).toHaveURL('/settings');
});

test('account forms reject invalid input', async ({ page }) => {
    await page.goto('/settings/password');
    await page.getByRole('button', { name: 'Confirmer' }).click();
    await expect(page.getByRole('alert')).toHaveText(
        'Les informations sont invalides',
    );

    await page.getByPlaceholder('Ancien mot de passe').fill('ancien');
    await page.getByPlaceholder('Nouveau mot de passe').fill('nouveau-mot');
    await page.getByPlaceholder('Confirmer le mot de passe').fill('autre-mot');
    await page.getByRole('button', { name: 'Confirmer' }).click();
    await expect(page.getByRole('alert')).toHaveText(
        'Les mots de passe ne correspondent pas',
    );
    await expect(page).toHaveURL('/settings/password');

    await page.goto('/settings/username');
    await page.getByPlaceholder("Nom d'utilisateur").fill('   ');
    await page.getByRole('button', { name: 'Confirmer' }).click();
    await expect(page.getByRole('alert')).toHaveText(
        'Les informations sont invalides',
    );
    await expect(page).toHaveURL('/settings/username');
});

test('account forms submit through the mocked API', async ({ page }) => {
    const updatedUser = { ...testUser, name: 'NouveauNom' };
    await page.route('**/sanctum/csrf-cookie', (route) =>
        route.fulfill({ status: 204 }),
    );
    await page.route('**/api/updateProfile', (route) => {
        const body = route.request().postDataJSON();
        if (body.password) {
            expect(body).toEqual({
                current_password: 'ancien',
                password: 'nouveau-mot',
                password_confirmation: 'nouveau-mot',
            });
            return route.fulfill({ status: 200, json: { user: testUser } });
        }
        expect(body).toEqual({ name: 'NouveauNom' });
        return route.fulfill({ status: 200, json: { user: updatedUser } });
    });

    await page.goto('/settings/password');
    await page.getByPlaceholder('Ancien mot de passe').fill('ancien');
    await page.getByPlaceholder('Nouveau mot de passe').fill('nouveau-mot');
    await page
        .getByPlaceholder('Confirmer le mot de passe')
        .fill('nouveau-mot');
    await page.getByRole('button', { name: 'Confirmer' }).click();
    await expect(page).toHaveURL('/settings');

    await page.goto('/settings/username');
    await page.getByPlaceholder("Nom d'utilisateur").fill('  NouveauNom  ');
    await page.getByRole('button', { name: 'Confirmer' }).click();
    await expect(page).toHaveURL('/settings');
    await expect(
        page.getByText("Nom d'utilisateur : NouveauNom"),
    ).toBeVisible();
});

test('account pages do not scroll horizontally on a phone', async ({
    page,
}) => {
    const longMessage =
        'Le nom d utilisateur est déjà utilisé par un autre compte et ne peut pas être enregistré tel quel.';
    await page.route('**/sanctum/csrf-cookie', (route) =>
        route.fulfill({ status: 204 }),
    );
    await page.route('**/api/updateProfile', (route) =>
        route.fulfill({ status: 422, json: { message: longMessage } }),
    );

    for (const viewport of viewports) {
        await page.setViewportSize(viewport);
        await page.goto('/settings');
        const direction = await page
            .locator('.settings-field', { hasText: "Nom d'utilisateur" })
            .evaluate((element) => getComputedStyle(element).flexDirection);
        expect(direction).toBe('column');
        const fieldBox = await page
            .locator('.settings-field', { hasText: "Nom d'utilisateur" })
            .boundingBox();
        const textBox = await page
            .getByText(`Nom d'utilisateur : ${testUser.name}`)
            .boundingBox();
        const buttonBox = await page
            .locator('.settings-field', { hasText: "Nom d'utilisateur" })
            .getByRole('button', { name: 'Modifier' })
            .boundingBox();
        expect(textBox.width).toBeLessThanOrEqual(fieldBox.width);
        expect(buttonBox.y).toBeGreaterThanOrEqual(
            textBox.y + textBox.height - 1,
        );
        await expectNoHorizontalScroll(page);

        await page.goto('/settings/password');
        await expectNoHorizontalScroll(page);

        await page.goto('/settings/username');
        await page.getByPlaceholder("Nom d'utilisateur").fill('AutreNom');
        await page.getByRole('button', { name: 'Confirmer' }).click();
        const banner = page.getByRole('alert');
        await expect(banner).toHaveText(longMessage);
        await expect(banner).toBeVisible();
        const bannerBox = await banner.boundingBox();
        expect(bannerBox.x).toBeGreaterThanOrEqual(0);
        expect(bannerBox.x + bannerBox.width).toBeLessThanOrEqual(
            viewport.width + 1,
        );
        await expectNoHorizontalScroll(page);
    }
});
