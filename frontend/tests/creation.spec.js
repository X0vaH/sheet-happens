import { test, expect } from './helpers/test.js';

test('requires a name before opening the race route directly', async ({
    page,
}) => {
    await page.goto('/character/create/race');
    await expect(page).toHaveURL(/\/character\/create$/);
});

test('existing navigation opens the creation screen', async ({ page }) => {
    await page.goto('/');
    await page.getByRole('link', { name: 'Créer un personnage' }).click();
    await expect(
        page.getByRole('heading', { name: 'Informations' }),
    ).toBeVisible();
});

for (const width of [1920, 768, 390, 320]) {
    test(
        'creation screens fit a viewport of ' + width + 'px',
        async ({ page }, testInfo) => {
            await page.setViewportSize({
                width,
                height: width === 1920 ? 1080 : 844,
            });
            await page.goto('/character/create');
            await page.evaluate(() => document.fonts.ready);
            await expect(page.getByLabel('Nom du personnage')).toBeVisible();
            expect(
                await page.evaluate(
                    () =>
                        document.documentElement.scrollWidth <=
                        window.innerWidth,
                ),
            ).toBe(true);
            await page.getByLabel('Nom du personnage').fill('Arthur');
            await expect(
                page.getByRole('button', { name: 'Continuer' }),
            ).toBeDisabled();
            await page
                .getByLabel('Alignement', { exact: true })
                .selectOption('Neutre bon');
            await expect(
                page.getByText(
                    'Cherche avant tout à faire le bien, sans être strictement attaché aux règles.',
                ),
            ).toBeVisible();
            await expect(
                page.getByRole('button', { name: 'Continuer' }),
            ).toBeEnabled();
            await page.getByRole('button', { name: 'Continuer' }).click();
            await expect(
                page.getByRole('heading', { name: 'Choix de la race' }),
            ).toBeVisible();
            expect(
                await page.evaluate(
                    () =>
                        document.documentElement.scrollWidth <=
                        window.innerWidth,
                ),
            ).toBe(true);
            await expect
                .poll(() =>
                    page
                        .locator('.creation-background')
                        .evaluate(
                            (img) => img.complete && img.naturalWidth > 0,
                        ),
                )
                .toBe(true);
            const tile = page.locator('.creation-background-tile').first();
            const tileHeight = await tile.evaluate(
                (img) => img.getBoundingClientRect().height,
            );
            expect(tileHeight).toBe(page.viewportSize().height);
            await expect(
                page.locator('.creation-background-tile').nth(1),
            ).toHaveCSS('transform', 'matrix(1, 0, 0, -1, 0, 0)');
            await page.locator('.creation-page').evaluate((element, height) => {
                element.style.minHeight = `${height * 3.5}px`;
            }, tileHeight);
            await expect(page.locator('.creation-background-tile')).toHaveCount(
                4,
            );
            expect(
                await tile.evaluate(
                    (img) => img.getBoundingClientRect().height,
                ),
            ).toBe(tileHeight);
            await page
                .locator('.creation-page')
                .evaluate((element) =>
                    element.style.removeProperty('min-height'),
                );
            await page.screenshot({
                path: testInfo.outputPath('race.png'),
                fullPage: true,
            });
            await page
                .getByRole('button', { name: 'Halfelin', exact: true })
                .click();
            await expect(
                page.getByRole('heading', { name: 'Halfelin', exact: true }),
            ).toBeVisible();
            await page.getByRole('button', { name: 'Continuer' }).click();
            await expect(
                page.getByRole('heading', { name: 'Choix de la classe' }),
            ).toBeVisible();
        },
    );
}
