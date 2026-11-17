import { test, expect } from './helpers/test.js';
import {
    openClass,
    selectEquipment,
    fillAbilityScores,
    selectSkills,
    completeBackground,
} from './helpers/characterCreation.js';
import { calculateArmorClass } from '../src/utils/characterCreation/armorClass.js';
import { creationOptions } from './fixtures/creationOptions.js';

const items = (names) =>
    names.map((name) =>
        creationOptions.items.find((item) => item.name === name),
    );
const armorClass = (characterClass, itemNames, scores, style = '') =>
    calculateArmorClass(
        creationOptions,
        characterClass,
        items(itemNames),
        scores,
        style,
    );

test('Defense requires fighter armor and does not treat a shield as armor', () => {
    const scores = [13, 16, 13, 13, 13, 13];
    const defense = (characterClass, itemNames) =>
        armorClass(characterClass, itemNames, scores, 'Défense');
    expect(defense('Guerrier', ['Cotte de mailles', 'Bouclier'])).toBe(19);
    expect(defense('Guerrier', ['Cotte de mailles'])).toBe(17);
    expect(defense('Guerrier', ['Bouclier'])).toBe(15);
    expect(defense('Clerc', ['Cotte de mailles', 'Bouclier'])).toBe(18);
    expect(
        armorClass(
            'Guerrier',
            ['Cotte de mailles', 'Bouclier'],
            scores,
            'Archerie',
        ),
    ).toBe(18);
});

test('fighter style must be reselected after class changes and is saved to the character', async ({
    page,
    api,
}) => {
    await page.setViewportSize({ width: 320, height: 900 });
    await openClass(page);
    const panel = page.getByRole('region', {
        name: 'Style de combat',
        exact: true,
    });
    await expect(panel.getByRole('button')).toHaveCount(6);
    await panel.getByRole('button', { name: 'Archerie', exact: false }).click();
    await panel.getByRole('button', { name: 'Défense', exact: false }).click();
    await expect(panel.locator('[aria-pressed="true"]')).toHaveCount(1);
    await expect(
        page.getByText('Style de combat : Défense', { exact: true }),
    ).toBeVisible();
    expect(
        await page.evaluate(
            () => document.documentElement.scrollWidth <= innerWidth,
        ),
    ).toBe(true);
    await page.getByRole('button', { name: 'Clerc', exact: true }).click();
    await expect(panel).toHaveCount(0);
    await expect(
        page.getByText('Style de combat : Défense', { exact: true }),
    ).toHaveCount(0);
    await page.getByRole('button', { name: 'Guerrier', exact: true }).click();
    await expect(
        panel.getByRole('button', { name: 'Défense', exact: false }),
    ).toHaveAttribute('aria-pressed', 'false');
    await expect(
        page.getByRole('button', { name: 'Continuer' }),
    ).toBeDisabled();
    await panel.getByRole('button', { name: 'Défense', exact: false }).click();
    await selectEquipment(page);
    await page.getByRole('button', { name: 'Continuer' }).click();
    await expect(page.locator('.abilities-derived strong').last()).toHaveText(
        '19',
    );
    await fillAbilityScores(page);
    await page.getByRole('button', { name: 'Continuer' }).click();
    await completeBackground(page);
    await selectSkills(page);
    await expect(
        page.getByText('Style de combat : Défense', { exact: true }),
    ).toBeVisible();
    await page.getByRole('tab', { name: 'Capacités et traits' }).click();
    await expect(page.getByRole('tabpanel')).toContainText(
        '+1 CA lorsque le personnage porte une armure.',
    );
    // The catalog now fully covers both the fighting style and the equipment: nothing unsaved.
    await expect(
        page.getByRole('region', { name: 'Choix non enregistrés' }),
    ).toHaveCount(0);
    await page.getByRole('button', { name: 'Sauvegarder' }).click();
    await expect(page).toHaveURL('/');
    expect(api.createdCharacters).toHaveLength(1);
    const [saved] = api.createdCharacters;
    expect(saved.subclass_id).toBeNull();
    const fightingStyleFeature = creationOptions.classes
        .find(({ name }) => name === 'Guerrier')
        .level_one.features.find(({ name }) => name === 'Style de combat');
    const defense = fightingStyleFeature.options.find(
        ({ name }) => name === 'Défense',
    );
    expect(saved.feature_choices).toContainEqual({
        feature_id: fightingStyleFeature.id,
        feature_option_id: defense.id,
    });
});
