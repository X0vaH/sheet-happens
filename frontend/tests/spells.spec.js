import { openSkills, selectSkills } from './helpers/characterCreation.js';
import { test, expect } from './helpers/test.js';

async function openSpells(page) {
    await openSkills(page, 'Magicien', 'Merlin');
    await selectSkills(page);
    await expect(page).toHaveURL(/spells$/);
    await expect(
        page.locator('.spells-picker [aria-pressed="true"]'),
    ).toHaveCount(0);
}
async function completeSpells(page) {
    for (const name of [
        'Main du mage',
        'Trait de feu',
        'Prestidigitation',
        'Armure du mage',
        'Charme-personne',
        'Bouclier',
        'Projectile magique',
        'Sommeil',
        'Détection de la magie',
    ])
        await page.getByRole('button', { name, exact: true }).click();
}

test('spell information opens on hover and closes with Escape', async ({
    page,
}) => {
    await openSpells(page);
    await page
        .getByRole('button', { name: 'Main du mage', exact: true })
        .hover();
    await expect(page.getByRole('tooltip')).toContainText('Main du mage');
    await page.keyboard.press('Escape');
    await expect(page.getByRole('tooltip')).toHaveCount(0);
});

test.describe('touch spell information', () => {
    test.use({ hasTouch: true, viewport: { width: 390, height: 844 } });
    test('information is independent of selection and closes on an outside tap', async ({
        page,
    }) => {
        await openSpells(page);
        const spell = page.getByRole('button', {
            name: 'Main du mage',
            exact: true,
        });
        await spell.tap();
        await expect(spell).toHaveAttribute('aria-pressed', 'true');
        await expect(page.getByRole('tooltip')).toHaveCount(0);
        await page
            .getByRole('button', {
                name: 'Informations sur Main du mage',
                exact: true,
            })
            .tap();
        await expect(page.getByRole('tooltip')).toContainText('Main du mage');
        await expect(spell).toHaveAttribute('aria-pressed', 'true');
        await page.locator('#cantrips-title').tap();
        await expect(page.getByRole('tooltip')).toHaveCount(0);
    });
});

for (const width of [1920, 1440, 768, 390, 320]) {
    test('spells responsive at ' + width, async ({ page }, testInfo) => {
        await page.setViewportSize({
            width,
            height: width === 1920 ? 1080 : 900,
        });
        await openSpells(page);
        await page.evaluate(() => document.fonts.ready);
        expect(
            await page.evaluate(
                () => document.documentElement.scrollWidth <= innerWidth,
            ),
        ).toBe(true);
        await page.screenshot({
            path: testInfo.outputPath('spells.png'),
            fullPage: true,
        });
        await completeSpells(page);
        await expect(
            page.getByRole('button', { name: 'Continuer' }),
        ).toBeEnabled();
    });
}
