import { openClass } from './helpers/characterCreation.js';
import { test, expect } from './helpers/test.js';

for (const width of [1920, 1440, 768, 390, 320]) {
    test('class layout at ' + width + 'px', async ({ page }, testInfo) => {
        await page.setViewportSize({ width, height: 900 });
        await openClass(page);
        await page.evaluate(() => document.fonts.ready);
        expect(
            await page.evaluate(
                () => document.documentElement.scrollWidth <= innerWidth,
            ),
        ).toBe(true);
        await expect(
            page.getByRole('heading', { name: 'Équipement', exact: true }),
        ).toHaveCount(0);
        await expect(
            page.getByRole('heading', { name: 'Style de combat', exact: true }),
        ).toBeVisible();
        await expect(
            page.getByRole('heading', { name: 'Maîtrises', exact: true }),
        ).toBeVisible();
        await expect(page.getByText('Toutes les armures')).toBeVisible();
        await page.screenshot({
            path: testInfo.outputPath('class.png'),
            fullPage: true,
        });
        await page.getByRole('button', { name: 'Continuer' }).click();
        await expect(page).toHaveURL(/equipment$/);
    });
}
