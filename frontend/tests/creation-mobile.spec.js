import { test, expect } from './helpers/test.js';

test('mobile menu supports escape and navigation to earlier steps', async ({
    page,
}) => {
    await page.setViewportSize({ width: 402, height: 900 });
    await page.goto('/character/create');
    await page.getByLabel('Nom du personnage').fill('Arthur');
    await page
        .getByLabel('Alignement', { exact: true })
        .selectOption('Neutre bon');
    await page.getByRole('button', { name: 'Continuer' }).click();
    const menu = page.locator('.creation-mobile-menu');
    const navMenu = page.locator('.creation-mobile-header .navbar-mobile-menu');
    await expect(menu).toHaveAccessibleName('Ouvrir le menu');
    await expect(menu).toHaveAttribute('aria-expanded', 'false');
    await expect(navMenu).toBeHidden();
    await menu.click();
    await expect(menu).toHaveAccessibleName('Fermer le menu');
    await expect(menu).toHaveAttribute('aria-expanded', 'true');
    await expect(navMenu).toBeVisible();
    await page.keyboard.press('Escape');
    await expect(menu).toHaveAccessibleName('Ouvrir le menu');
    await expect(menu).toHaveAttribute('aria-expanded', 'false');
    await expect(navMenu).toBeHidden();
    await expect(menu).toBeFocused();
    await expect(page.locator('.creation-steps')).toBeVisible();
    await page
        .locator('.creation-steps')
        .getByRole('link', { name: 'Informations', exact: true })
        .click();
    await expect(page).toHaveURL('/character/create');
    await expect(page.locator('.creation-steps')).toBeVisible();
});
