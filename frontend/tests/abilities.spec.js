import { openAbilities } from './helpers/characterCreation.js';
import { test, expect } from './helpers/test.js';
import { creationOptions } from './fixtures/creationOptions.js';
import { abilityIndex, names } from '../src/utils/characterCreation/catalog.js';

test('direct access requires completion of earlier steps', async ({ page }) => {
    await page.goto('/character/create/abilities');
    await expect(page).toHaveURL(/\/character\/create$/);
});

test('standard results can be assigned, released and restored without reuse', async ({
    page,
}) => {
    await openAbilities(page, 'Roublard');
    await page.getByRole('button', { name: 'Standard', exact: true }).click();
    const inputs = page.getByRole('spinbutton');
    const next = page.getByRole('button', { name: 'Valeur suivante pour' });
    await expect(
        page.getByRole('button', { name: 'Continuer' }),
    ).toBeDisabled();
    await next.nth(0).click();
    await expect(inputs.nth(0)).toHaveValue('15');
    await next.nth(1).click();
    await expect(inputs.nth(1)).toHaveValue('14');
    await page
        .getByRole('button', {
            name: 'Valeur précédente pour Force',
            exact: true,
        })
        .click();
    await expect(inputs.nth(0)).toHaveValue('');
    await page
        .getByRole('button', {
            name: 'Valeur précédente pour Dextérité',
            exact: true,
        })
        .click();
    await expect(inputs.nth(1)).toHaveValue('15');
    for (const index of [0, 2, 3, 4, 5]) await next.nth(index).click();
    await expect(inputs.nth(0)).toHaveValue('14');
    await expect(page.getByRole('button', { name: 'Continuer' })).toBeEnabled();
    await page.getByRole('button', { name: 'Continuer' }).click();
    await page.getByRole('link', { name: 'Retour', exact: true }).click();
    await expect(inputs.nth(0)).toHaveValue('14');
    await expect(inputs.nth(1)).toHaveValue('15');
    await expect(page.getByRole('button', { name: 'Continuer' })).toBeEnabled();
});

for (const method of ['Dés']) {
    test(`${method} preserves repeated results and resets assignments on a new roll`, async ({
        page,
    }) => {
        await openAbilities(page, 'Roublard');
        await page.evaluate(() => {
            Math.random = () => 0.5;
        });
        await page.getByRole('button', { name: method, exact: true }).click();
        for (const button of await page
            .getByRole('button', { name: 'Valeur suivante pour' })
            .all())
            await button.click();
        for (const input of await page.getByRole('spinbutton').all()) {
            await expect(input).toHaveValue('12');
            await expect(input).toHaveAttribute('readonly', '');
        }
        await expect(
            page.getByRole('button', { name: 'Continuer' }),
        ).toBeEnabled();
        await page
            .getByRole('button', { name: 'Standard', exact: true })
            .click();
        for (const input of await page.getByRole('spinbutton').all())
            await expect(input).toHaveValue('');
        await expect(
            page.getByRole('button', { name: 'Continuer' }),
        ).toBeDisabled();
    });
}

test('manual scores hide the pool, accept bounds and persist through navigation', async ({
    page,
}) => {
    await openAbilities(page, 'Roublard');
    await page.getByRole('button', { name: 'Standard', exact: true }).click();
    await expect(page.locator('.abilities-message')).toBeVisible();
    await page.getByRole('button', { name: 'Manuel', exact: true }).click();
    await expect(page.locator('.abilities-message')).toHaveCount(0);
    const inputs = page.getByRole('spinbutton');
    for (let index = 0; index < 6; index++)
        await inputs.nth(index).fill(index % 2 ? '20' : '3');
    await page.getByRole('button', { name: 'Continuer' }).click();
    await expect(page).toHaveURL(/background$/);
    await page.getByRole('link', { name: 'Retour', exact: true }).click();
    await expect(
        page.getByRole('button', { name: 'Manuel', exact: true }),
    ).toHaveAttribute('aria-pressed', 'true');
    await expect(inputs.nth(0)).toHaveValue('3');
    await expect(inputs.nth(1)).toHaveValue('20');
    await expect(page.locator('.abilities-message')).toHaveCount(0);
    await page.getByRole('button', { name: 'Standard', exact: true }).click();
    await expect(page.locator('.abilities-message')).toBeVisible();
    for (const input of await inputs.all()) {
        await expect(input).toHaveValue('');
        await expect(input).toHaveAttribute('readonly', '');
    }
});

test('manual invalid, empty and fractional scores open the red popup on continue', async ({
    page,
}) => {
    await openAbilities(page, 'Roublard');
    await page.getByRole('button', { name: 'Manuel', exact: true }).click();
    const inputs = page.getByRole('spinbutton');
    for (const input of await inputs.all()) await input.fill('10');
    for (const value of ['21', '2', '', '10.5']) {
        await inputs.first().fill(value);
        await page
            .getByRole('button', { name: 'Continuer' })
            .click({ force: true });
        const dialog = page.getByRole('dialog', { name: 'Choix manquants' });
        await expect(dialog).toContainText(
            'Force : saisissez un nombre entier entre 3 et 20.',
        );
        await expect(dialog).toHaveCSS('background-color', 'rgb(130, 29, 41)');
        await expect(page).toHaveURL(/abilities$/);
        await dialog.getByRole('button', { name: 'Fermer' }).click();
    }
    await inputs.first().fill('20');
    await page.getByRole('button', { name: 'Continuer' }).click();
    await expect(page).toHaveURL(/background$/);
});

test('manual scores block non-digit keys while digits stay typable', async ({
    page,
}) => {
    await openAbilities(page, 'Roublard');
    await page.getByRole('button', { name: 'Manuel', exact: true }).click();
    const input = page.getByRole('spinbutton').first();
    const isPrevented = (key) =>
        input.evaluate((element, pressed) => {
            const event = new KeyboardEvent('keydown', {
                key: pressed,
                bubbles: true,
                cancelable: true,
            });
            element.dispatchEvent(event);
            return event.defaultPrevented;
        }, key);
    for (const key of ['-', '+', 'e', 'E', '.', ','])
        expect(await isPrevented(key), `"${key}" should be blocked`).toBe(true);
    for (const key of ['0', '5', 'Backspace', 'Tab'])
        expect(await isPrevented(key), `"${key}" should be allowed`).toBe(
            false,
        );
    await input.click();
    await page.keyboard.type('1-+e.,5');
    await expect(input).toHaveValue('15');
});

for (const width of [1920, 1440, 768, 390, 320]) {
    test('abilities layout at ' + width + 'px', async ({ page }, testInfo) => {
        await page.setViewportSize({
            width,
            height: width === 1920 ? 1080 : 900,
        });
        await openAbilities(page, 'Roublard');
        await page.evaluate(() => document.fonts.ready);
        expect(
            await page.evaluate(
                () => document.documentElement.scrollWidth <= innerWidth,
            ),
        ).toBe(true);
        await expect(page.getByRole('spinbutton')).toHaveCount(6);
        for (const img of await page.locator('.ability-modifier img').all()) {
            expect(
                await img.evaluate(
                    (element) => element.complete && element.naturalWidth > 0,
                ),
            ).toBe(true);
            const box = await img.boundingBox();
            expect(box.width).toBeCloseTo(width <= 620 ? 79 : 90.91, 1);
            expect(box.height).toBeCloseTo(width <= 620 ? 42 : 44.3097, 1);
        }
        await page.screenshot({
            path: testInfo.outputPath('abilities.png'),
            fullPage: true,
        });
    });
}

test('catalog ability order matches the index order of saved drafts', () => {
    expect(names(creationOptions.abilities)).toEqual([
        'Force',
        'Dextérité',
        'Constitution',
        'Intelligence',
        'Sagesse',
        'Charisme',
    ]);
    expect(abilityIndex(creationOptions, creationOptions.abilities[2].id)).toBe(
        2,
    );
});
