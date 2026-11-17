import { test, expect } from './helpers/test.js';
import {
    selectEquipment,
    fillAbilityScores,
    selectSkills,
} from './helpers/characterCreation.js';

async function expectMissing(page, items, keyboard = false) {
    const button = page.getByRole('button', { name: 'Continuer', exact: true });
    const url = page.url();
    await expect(button).toHaveAttribute('aria-disabled', 'true');
    if (keyboard) {
        await button.focus();
        await page.keyboard.press('Enter');
    } else {
        // The button deliberately accepts clicks to explain its disabled state.
        await button.click({ force: true });
    }
    const dialog = page.getByRole('dialog', { name: 'Choix manquants' });
    await expect(dialog).toBeVisible();
    for (const item of items) await expect(dialog).toContainText(item);
    await expect(page).toHaveURL(url);
    await page.keyboard.press('Escape');
    await expect(dialog).not.toBeVisible();
    await expect(button).toBeFocused();
}

test('incomplete creation steps explain missing choices without navigating', async ({
    page,
}) => {
    await page.goto('/character/create');
    await expectMissing(page, ['Nom du personnage', 'Alignement'], true);
    await page.getByLabel('Nom du personnage').fill('Arthur');
    await page
        .getByLabel('Alignement', { exact: true })
        .selectOption('Neutre bon');
    await page.getByRole('button', { name: 'Continuer' }).click();
    await expectMissing(page, ['Race du personnage']);
    await page.getByRole('button', { name: 'Humain', exact: true }).click();
    await page.getByRole('button', { name: 'Continuer' }).click();
    await expectMissing(page, ['Classe du personnage']);
    await page.getByRole('button', { name: 'Clerc', exact: true }).click();
    await expectMissing(page, ['Sous-classe']);
    await page.getByRole('button', { name: /Domaine de la Vie/ }).click();
    await page.getByRole('button', { name: 'Continuer' }).click();
    await expectMissing(page, ['Arme', 'Armure', 'Équipement d’aventurier']);
    await selectEquipment(page);
    await page.getByRole('button', { name: 'Continuer' }).click();
    await expectMissing(page, ['méthode']);
    await fillAbilityScores(page);
    await page.getByRole('button', { name: 'Continuer' }).click();
    // The language count comes from the chosen background, so no "Langue N" is missing yet.
    await expectMissing(page, ['Historique']);
    await page
        .getByRole('combobox', { name: 'Choisir un historique' })
        .selectOption('Sage');
    await expectMissing(page, ['Langue 1', 'Langue 2']);
    await page.locator('#language-0').selectOption({ index: 1 });
    await expectMissing(page, ['Langue 2']);
    await page.locator('#language-1').selectOption({ index: 1 });
    await page.getByRole('button', { name: 'Continuer' }).click();
    await expectMissing(page, ['2 compétence(s)']);
    await selectSkills(page);
    await expectMissing(page, ['3 sort(s) mineur(s)', '4 sort(s) de niveau 1']);
});

for (const width of [1920, 320]) {
    test(
        'navigation buttons are compact and centered at ' + width,
        async ({ page }) => {
            await page.setViewportSize({ width, height: 900 });
            await page.goto('/character/create');
            const actions = await page.locator('.race-actions').boundingBox();
            expect(
                Math.abs(actions.x + actions.width / 2 - width / 2),
            ).toBeLessThan(2);
            for (const button of await page
                .locator('.race-actions button')
                .all()) {
                const box = await button.boundingBox();
                expect(box.width).toBeLessThanOrEqual(160);
                expect(box.height).toBeLessThanOrEqual(60);
            }
            await expectMissing(page, ['Nom du personnage']);
            expect(
                await page.evaluate(
                    () => document.documentElement.scrollWidth <= innerWidth,
                ),
            ).toBe(true);
        },
    );
}
