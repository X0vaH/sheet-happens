import { openSkills } from './helpers/characterCreation.js';
import { test, expect } from './helpers/test.js';

test('direct skills access validates the previous steps', async ({ page }) => {
    await page.goto('/character/create/skills');
    await expect(page).toHaveURL(/\/character\/create$/);
});

test('selecting a skill does not change ability scores or modifiers', async ({
    page,
}) => {
    await openSkills(page);
    const skill = page.getByRole('button', { name: 'Acrobaties', exact: true });
    const scores = await page
        .locator('.skill-ability > strong')
        .allTextContents();
    const modifiers = await page
        .locator('.skill-ability-modifier span')
        .allTextContents();
    const base = await skill.locator('.skill-base-modifier').textContent();
    await skill.click();
    await expect(skill.locator('.skill-base-modifier')).toHaveText(base);
    await expect(skill).toHaveAttribute('aria-pressed', 'true');
    expect(
        await page.locator('.skill-ability > strong').allTextContents(),
    ).toEqual(scores);
    expect(
        await page.locator('.skill-ability-modifier span').allTextContents(),
    ).toEqual(modifiers);
    await skill.click();
    await expect(skill).toHaveAttribute('aria-pressed', 'false');
    await expect(skill.locator('.skill-base-modifier')).toHaveText(base);
});

for (const width of [1920, 1440, 768, 390, 320]) {
    test('skills layout at ' + width + 'px', async ({ page }, testInfo) => {
        await page.setViewportSize({
            width,
            height: width === 1920 ? 1080 : 900,
        });
        await openSkills(page);
        await page.evaluate(() => document.fonts.ready);
        expect(
            await page.evaluate(
                () => document.documentElement.scrollWidth <= innerWidth,
            ),
        ).toBe(true);
        await expect(page.locator('.skills-choices button')).toHaveCount(18);
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
            path: testInfo.outputPath('skills.png'),
            fullPage: true,
        });
    });
}
