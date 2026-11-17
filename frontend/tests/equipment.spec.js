import { test, expect } from './helpers/test.js';
import {
    openClass,
    selectEquipment,
    fillAbilityScores,
    completeBackground,
    selectSkills,
} from './helpers/characterCreation.js';
import { creationOptions } from './fixtures/creationOptions.js';

for (const width of [1920, 1440, 768, 390, 320]) {
    test('equipment panels fit ' + width + 'px', async ({ page }, testInfo) => {
        await page.setViewportSize({ width, height: 900 });
        await openClass(page);
        await page.getByRole('button', { name: 'Continuer' }).click();
        await expect(
            page.getByRole('button', { name: 'Continuer' }),
        ).toBeDisabled();
        await selectEquipment(page);
        expect(
            await page.evaluate(
                () => document.documentElement.scrollWidth <= innerWidth,
            ),
        ).toBe(true);
        await page.screenshot({
            path: testInfo.outputPath('equipment.png'),
            fullPage: true,
        });
        await page.getByRole('button', { name: 'Continuer' }).click();
        await expect(page).toHaveURL(/abilities$/);
        await page.getByRole('link', { name: 'Retour', exact: true }).click();
        await expect(
            page.getByRole('button', { name: 'Cotte de mailles', exact: true }),
        ).toHaveAttribute('aria-pressed', 'true');
    });
}

test('equipment choices persist, appear in the summary and are saved to the character', async ({
    page,
    api,
}) => {
    await openClass(page);
    await selectEquipment(page);
    await page.getByRole('button', { name: 'Continuer' }).click();
    await fillAbilityScores(page);
    await page.getByRole('button', { name: 'Continuer' }).click();
    await completeBackground(page);
    await selectSkills(page);
    await expect(page.getByRole('tabpanel')).toContainText('Cotte de mailles');
    const equipmentList = page
        .getByRole('tabpanel')
        .locator('.equipment-item-list');
    await expect(equipmentList).toHaveCount(1);
    expect(
        await equipmentList.evaluate((list) => {
            const rows = [...list.children];
            const left = rows[0].getBoundingClientRect().left;
            return rows.every((row) => {
                const description = row.querySelector(
                    '.equipment-item-description',
                );
                return (
                    row.getBoundingClientRect().left === left &&
                    getComputedStyle(row).marginTop === '0px' &&
                    (!description ||
                        (getComputedStyle(description).display === 'inline' &&
                            description.textContent.startsWith(' : ')))
                );
            });
        }),
    ).toBe(true);
    // The catalog fully covers starting equipment now: nothing is listed as unsaved.
    await expect(
        page.getByRole('region', { name: 'Choix non enregistrés' }),
    ).toHaveCount(0);
    await page.getByRole('button', { name: 'Sauvegarder' }).click();
    await expect(page).toHaveURL('/');
    const guerrier = creationOptions.classes.find(
        ({ name }) => name === 'Guerrier',
    );
    const firstOptionIds = guerrier.starting_equipment_groups
        .map((group) => group.options[0].id)
        .sort();
    const savedOptionIds = api.createdCharacters[0].starting_equipment_choices
        .map(({ starting_equipment_option_id: id }) => id)
        .sort();
    expect(savedOptionIds).toEqual(firstOptionIds);
});

test('equipment cannot be bypassed', async ({ page }) => {
    await openClass(page);
    await page.evaluate(() => {
        history.pushState({}, '', '/character/create/abilities');
        dispatchEvent(new PopStateEvent('popstate'));
    });
    await expect(page).toHaveURL(/equipment$/);
});
