import {
    openSkills,
    selectSkills,
    selectEquipment,
    pickClass,
} from './helpers/characterCreation.js';
import { test, expect } from './helpers/test.js';

async function openCleric(page) {
    await openSkills(page, 'Clerc', 'Éric');
    await selectSkills(page);
    await expect(page).toHaveURL(/spells$/);
    await expect(
        page.locator('.spells-picker [aria-pressed="true"]'),
    ).toHaveCount(0);
}
async function completeCleric(page) {
    for (const name of [
        'Lumière',
        'Thaumaturgie',
        'Flamme sacrée',
        'Soins',
        'Bénédiction',
        'Détection de la magie',
        'Sanctuaire',
    ])
        await page.getByRole('button', { name, exact: true }).click();
}
async function switchClass(page, characterClass) {
    await page.getByRole('link', { name: 'Classe', exact: true }).click();
    await pickClass(page, characterClass);
    await selectEquipment(page);
    for (let i = 0; i < 4; i++)
        await page.getByRole('button', { name: 'Continuer' }).click();
    await expect(page).toHaveURL(/spells$/);
}

test('cleric and wizard selections stay separate when changing class', async ({
    page,
}) => {
    await openCleric(page);
    await completeCleric(page);
    await switchClass(page, 'Magicien');
    await expect(page.getByText('0 / 3 sélectionnés')).toBeVisible();
    await expect(page.getByText('0 / 6 sélectionnés')).toBeVisible();
    await page
        .getByRole('button', { name: 'Trait de feu', exact: true })
        .click();
    await page.getByRole('button', { name: 'Bouclier', exact: true }).click();
    await switchClass(page, 'Clerc');
    await expect(page.getByText('3 / 3 sélectionnés')).toBeVisible();
    await expect(page.getByText('4 / 4 sélectionnés')).toBeVisible();
    await expect(
        page.getByRole('button', { name: 'Flamme sacrée', exact: true }),
    ).toHaveAttribute('aria-pressed', 'true');
    await switchClass(page, 'Magicien');
    await expect(
        page.getByRole('button', { name: 'Trait de feu', exact: true }),
    ).toHaveAttribute('aria-pressed', 'true');
    await expect(
        page.getByRole('button', { name: 'Bouclier', exact: true }),
    ).toHaveAttribute('aria-pressed', 'true');
});

for (const width of [1920, 1440, 768, 390, 320]) {
    test('cleric layout at ' + width, async ({ page }, testInfo) => {
        await page.setViewportSize({
            width,
            height: width === 1920 ? 1080 : 900,
        });
        await openCleric(page);
        await page.evaluate(() => document.fonts.ready);
        expect(
            await page.evaluate(
                () => document.documentElement.scrollWidth <= innerWidth,
            ),
        ).toBe(true);
        await page.screenshot({
            path: testInfo.outputPath('cleric.png'),
            fullPage: true,
        });
        await completeCleric(page);
        await expect(
            page.getByRole('button', { name: 'Continuer' }),
        ).toBeEnabled();
    });
}
