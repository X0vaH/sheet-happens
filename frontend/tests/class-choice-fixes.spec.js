import { test, expect } from './helpers/test.js';
import {
    openClass,
    openAbilities,
    fillAbilityScores,
    completeBackground,
} from './helpers/characterCreation.js';

// Both are level 1 catalog choices: fighting style (Guerrier) is a feature with several
// FeatureOption, subclass (e.g. Clerc's Domaine de la Vie) comes from level_one.subclasses.
async function completeClassChoice(page) {
    for (const title of ['Style de combat', 'Sous-classe']) {
        const region = page.getByRole('region', { name: title, exact: true });
        if (await region.count())
            await region.getByRole('button').first().click();
    }
}

for (const characterClass of [
    'Guerrier',
    'Clerc',
    'Barbare',
    'Magicien',
    'Roublard',
]) {
    test(`${characterClass} requires only its own class choices`, async ({
        page,
    }) => {
        await openClass(page, characterClass, 'Arthur', false);
        const proceed = page.getByRole('button', { name: 'Continuer' });
        const required =
            characterClass === 'Guerrier'
                ? 'Style de combat'
                : characterClass === 'Clerc'
                  ? 'Sous-classe'
                  : null;
        for (const title of ['Style de combat', 'Sous-classe']) {
            await expect(
                page.getByRole('region', { name: title, exact: true }),
            ).toHaveCount(title === required ? 1 : 0);
        }
        if (required) {
            await expect(proceed).toBeDisabled();
            await proceed.click({ force: true });
            const dialog = page.getByRole('dialog', {
                name: 'Choix manquants',
            });
            await expect(dialog.locator('li')).toHaveText([required]);
            await dialog.getByRole('button', { name: 'Fermer' }).click();
            await completeClassChoice(page);
        }
        await expect(proceed).toBeEnabled();
        await proceed.click();
        await expect(page).toHaveURL(/equipment$/);
    });
}

test('changing to another class removes unrelated style and subclass requirements', async ({
    page,
}) => {
    await openClass(page, 'Guerrier', 'Arthur', false);
    const proceed = page.getByRole('button', { name: 'Continuer' });
    for (const characterClass of ['Barbare', 'Magicien', 'Roublard']) {
        for (const previousClass of ['Guerrier', 'Clerc']) {
            await page
                .getByRole('button', { name: previousClass, exact: true })
                .click();
            await expect(proceed).toBeDisabled();
            await page
                .getByRole('button', { name: characterClass, exact: true })
                .click();
            await expect(proceed).toBeEnabled();
            await expect(
                page.getByRole('region', {
                    name: 'Style de combat',
                    exact: true,
                }),
            ).toHaveCount(0);
            await expect(
                page.getByRole('region', { name: 'Sous-classe', exact: true }),
            ).toHaveCount(0);
        }
    }
    await proceed.click();
    await expect(page).toHaveURL(/equipment$/);
});

for (const [characterClass, limit] of [
    ['Roublard', 4],
    ['Guerrier', 2],
]) {
    test(`${characterClass} completes creation with its class skill limit`, async ({
        page,
    }) => {
        await openAbilities(page, characterClass);
        await fillAbilityScores(page);
        await expect(
            page.locator('.ability-modifier output').first(),
        ).toHaveText('+ 2');
        await page.getByRole('button', { name: 'Continuer' }).click();
        await completeBackground(page);
        const choices = page.locator('.skills-choices button:not(:disabled)');
        for (let index = 0; index < limit; index++) {
            await expect(
                page.getByRole('button', { name: 'Continuer' }),
            ).toBeDisabled();
            await choices.nth(index).click();
        }
        await choices.nth(limit).click({ force: true });
        await expect(
            page.locator(
                '.skills-choices [aria-pressed="true"]:not(:disabled)',
            ),
        ).toHaveCount(limit);
        await page.getByRole('button', { name: 'Continuer' }).click();
        await expect(page).toHaveURL(/summary$/);
        await page.getByRole('link', { name: 'Retour', exact: true }).click();
        await expect(
            page.locator(
                '.skills-choices [aria-pressed="true"]:not(:disabled)',
            ),
        ).toHaveCount(limit);
        await page.getByRole('button', { name: 'Continuer' }).click();
        await expect(page).toHaveURL(/summary$/);
        await page.getByRole('button', { name: 'Sauvegarder' }).click();
        await expect(page).toHaveURL('/');
    });
}
