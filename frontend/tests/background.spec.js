import {
    openAbilities,
    fillAbilityScores,
    selectSkills,
} from './helpers/characterCreation.js';
import { test, expect } from './helpers/test.js';
import { creationOptions } from './fixtures/creationOptions.js';

async function openBackground(page) {
    await openAbilities(page);
    await fillAbilityScores(page);
    await page.getByRole('button', { name: 'Continuer' }).click();
    await expect(
        page.getByRole('heading', { name: 'Historique', exact: true }),
    ).toBeVisible();
}

test('background route requires previous steps', async ({ page }) => {
    await page.goto('/character/create/background');
    await expect(page).toHaveURL(/\/character\/create$/);
});

test('language choice updates its description and persists through summary and save', async ({
    page,
    api,
}) => {
    await openBackground(page);
    // Guide grants one free language choice (Soldat, used below in the layout test, grants none).
    await page
        .getByRole('combobox', { name: 'Choisir un historique' })
        .selectOption('Guide');
    const language = page.getByRole('combobox', { name: 'Choisir une langue' });
    await expect(language).toHaveValue('');
    await expect(language.locator('option')).toHaveCount(16);
    await language.selectOption('Abyssal');
    await expect(page.locator('#language-description-0')).toHaveText(
        'Langue liée aux démons et aux créatures originaires des Abysses.',
    );
    await language.selectOption('Elfique');
    await expect(page.locator('#language-description-0')).toContainText(
        'fluide et mélodieuse',
    );
    await page.getByRole('button', { name: 'Continuer' }).click();
    await selectSkills(page);
    await page.getByRole('tab', { name: 'Langues', exact: true }).click();
    await expect(page.getByRole('tabpanel')).toContainText(
        'Langues parlées : Commun, Elfique',
    );
    await page.getByRole('link', { name: 'Retour', exact: true }).click();
    await page.getByRole('link', { name: 'Retour', exact: true }).click();
    await expect(language).toHaveValue('Elfique');
    await page.getByRole('button', { name: 'Continuer' }).click();
    await page.getByRole('button', { name: 'Continuer' }).click();
    await page.getByRole('button', { name: 'Sauvegarder' }).click();
    await expect(page).toHaveURL('/');
    const elvish = creationOptions.languages.find(
        ({ name }) => name === 'Elfique',
    );
    expect(api.createdCharacters[0].languages).toContainEqual({
        language_id: elvish.id,
        source: 'choice',
    });
});

for (const width of [1920, 1440, 768, 390, 320]) {
    test('background layout at ' + width + 'px', async ({ page }, testInfo) => {
        await page.setViewportSize({
            width,
            height: width === 1920 ? 1080 : 900,
        });
        await openBackground(page);
        await page
            .getByRole('combobox', { name: 'Choisir un historique' })
            .selectOption('Guide');
        await page
            .getByRole('combobox', { name: 'Choisir une langue' })
            .selectOption('Sous-commun');
        await expect(page.locator('#language-description-0')).toBeVisible();
        await page.evaluate(() => document.fonts.ready);
        expect(
            await page.evaluate(
                () => document.documentElement.scrollWidth <= innerWidth,
            ),
        ).toBe(true);
        await expect(page.locator('.skill-ability')).toHaveCount(6);
        await expect(
            page.locator('.background-selected-skills > div'),
        ).toHaveCount(2);
        for (const img of await page
            .locator('.skill-ability-modifier img')
            .all()) {
            expect(
                await img.evaluate(
                    (element) => element.complete && element.naturalWidth > 0,
                ),
            ).toBe(true);
            const box = await img.boundingBox();
            expect(box.width).toBeCloseTo(width <= 620 ? 78 : 47, 1);
            expect(box.height).toBeCloseTo(width <= 620 ? 37 : 22.8372, 1);
        }
        await page.screenshot({
            path: testInfo.outputPath('background.png'),
            fullPage: true,
        });
    });
}
