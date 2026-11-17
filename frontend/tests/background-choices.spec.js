import { test, expect } from './helpers/test.js';
import {
    openAbilities,
    fillAbilityScores,
    selectSkills,
} from './helpers/characterCreation.js';
import {
    languageChoiceCount,
    hasCompleteLanguages,
    normalizeLanguages,
} from '../src/utils/characterCreation/characterLanguages.js';
import {
    backgroundSkills,
    characterSkills,
} from '../src/utils/characterCreation/characterSkills.js';
import { creationOptions as options } from './fixtures/creationOptions.js';

const nameOf = (list, id) => list.find((entry) => entry.id === id)?.name;

test('background.language_choice_count drives the number of free language picks', () => {
    expect(languageChoiceCount(options, 'Humain', 'Acolyte')).toBe(2);
    expect(languageChoiceCount(options, 'Demi-elfe', 'Sage')).toBe(2);
    expect(languageChoiceCount(options, 'Elfe', 'Soldat')).toBe(0);
    expect(languageChoiceCount(options, 'Nain', 'Guide')).toBe(1);
    expect(
        hasCompleteLanguages(options, 'Humain', 'Sage', ['Abyssal', 'Céleste']),
    ).toBe(true);
    expect(
        hasCompleteLanguages(options, 'Humain', 'Sage', ['Abyssal', 'Abyssal']),
    ).toBe(false);
    expect(
        hasCompleteLanguages(options, 'Humain', 'Sage', ['Commun', 'Céleste']),
    ).toBe(false);
    expect(
        normalizeLanguages(options, 'Elfe', 'Soldat', ['Abyssal', 'Céleste']),
    ).toEqual([]);
});

test('all backgrounds grant their two skills', () => {
    const expected = {
        Acolyte: ['Perspicacité', 'Religion'],
        Soldat: ['Athlétisme', 'Intimidation'],
        Sage: ['Arcanes', 'Histoire'],
        Criminel: ['Discrétion', 'Tromperie'],
        Guide: ['Athlétisme', 'Survie'],
    };
    for (const [background, skills] of Object.entries(expected))
        expect(backgroundSkills(options, background)).toEqual(skills);
    expect(
        characterSkills(options, 'Soldat', ['Athlétisme', 'Acrobaties']),
    ).toEqual(['Athlétisme', 'Intimidation', 'Acrobaties']);
});

test('multiple languages and background skills persist through the reordered flow', async ({
    page,
    api,
}) => {
    await page.setViewportSize({ width: 320, height: 900 });
    await openAbilities(page);
    await fillAbilityScores(page);
    await page.getByRole('button', { name: 'Continuer' }).click();
    await expect(page).toHaveURL(/background$/);
    await page
        .getByRole('combobox', { name: 'Choisir un historique' })
        .selectOption('Sage');
    const choices = page.locator('.language-choice select');
    await expect(choices).toHaveCount(2);
    await choices.nth(0).selectOption('Abyssal');
    await expect(
        page.getByRole('button', { name: 'Continuer' }),
    ).toBeDisabled();
    await choices.nth(1).selectOption('Céleste');
    await expect(choices.nth(1).locator('option[value="Abyssal"]')).toHaveCount(
        0,
    );
    expect(
        await page.evaluate(
            () => document.documentElement.scrollWidth <= innerWidth,
        ),
    ).toBe(true);
    await page.getByRole('button', { name: 'Continuer' }).click();
    await expect(page).toHaveURL(/skills$/);
    await expect(
        page.getByRole('button', { name: 'Arcanes', exact: true }),
    ).toBeDisabled();
    await expect(
        page.getByRole('button', { name: 'Histoire', exact: true }),
    ).toBeDisabled();
    await selectSkills(page);
    await expect(page).toHaveURL(/summary$/);
    await page.getByRole('tab', { name: 'Langues', exact: true }).click();
    await expect(page.getByRole('tabpanel')).toContainText(
        'Commun, Abyssal, Céleste',
    );
    await expect(page.locator('.background-selected-skills > div')).toHaveCount(
        4,
    );
    await page.getByRole('button', { name: 'Sauvegarder' }).click();
    await expect(page).toHaveURL('/');
    const [saved] = api.createdCharacters;
    expect(
        saved.languages.map(({ language_id: id, source }) => [
            nameOf(options.languages, id),
            source,
        ]),
    ).toEqual([
        ['Commun', 'species'],
        ['Abyssal', 'choice'],
        ['Céleste', 'choice'],
    ]);
    expect(
        saved.skill_proficiencies.map(({ skill_id: id }) =>
            nameOf(options.skills, id),
        ),
    ).toEqual(['Arcanes', 'Histoire', 'Acrobaties', 'Athlétisme']);
});

test('changing background removes conflicting class skills and excess languages', async ({
    page,
}) => {
    await openAbilities(page);
    await fillAbilityScores(page);
    await page.getByRole('button', { name: 'Continuer' }).click();
    await page
        .getByRole('combobox', { name: 'Choisir un historique' })
        .selectOption('Sage');
    for (const [index, value] of ['Abyssal', 'Céleste'].entries())
        await page
            .locator('.language-choice select')
            .nth(index)
            .selectOption(value);
    await page.getByRole('button', { name: 'Continuer' }).click();
    await page.getByRole('button', { name: 'Athlétisme', exact: true }).click();
    await page.getByRole('button', { name: 'Acrobaties', exact: true }).click();
    await page.getByRole('link', { name: 'Retour', exact: true }).click();
    await page
        .getByRole('combobox', { name: 'Choisir un historique' })
        .selectOption('Soldat');
    // Soldat grants no free language choice, unlike Sage.
    await expect(page.locator('.language-choice select')).toHaveCount(0);
    await page.getByRole('button', { name: 'Continuer' }).click();
    await expect(
        page.getByRole('button', { name: 'Athlétisme', exact: true }),
    ).toBeDisabled();
    await expect(page.locator('#skills-count')).toHaveText(
        '1 / 2 sélectionnées',
    );
    await expect(
        page.getByRole('button', { name: 'Continuer' }),
    ).toBeDisabled();
});

test('changing race removes a now-known language from the choice list', async ({
    page,
}) => {
    await openAbilities(page);
    await fillAbilityScores(page);
    await page.getByRole('button', { name: 'Continuer' }).click();
    await page
        .getByRole('combobox', { name: 'Choisir un historique' })
        .selectOption('Guide');
    await page.locator('.language-choice select').selectOption('Elfique');
    await page.getByRole('link', { name: 'Race', exact: true }).click();
    await page.getByRole('button', { name: 'Elfe', exact: true }).click();
    for (let step = 0; step < 4; step++)
        await page.getByRole('button', { name: 'Continuer' }).click();
    await expect(page).toHaveURL(/background$/);
    // Guide still grants one free choice, but Elfique is now known racially: the previous
    // pick was dropped and the option no longer offered.
    await expect(page.locator('.language-choice select')).toHaveCount(1);
    await expect(page.locator('.language-choice select')).toHaveValue('');
    await expect(
        page.locator('.language-choice select option[value="Elfique"]'),
    ).toHaveCount(0);
    await expect(
        page.getByText('Langues connues : Commun, Elfique'),
    ).toBeVisible();
});

test('a background with no language choices needs none selected to continue', async ({
    page,
}) => {
    await openAbilities(page);
    await fillAbilityScores(page);
    await page.getByRole('button', { name: 'Continuer' }).click();
    await page
        .getByRole('combobox', { name: 'Choisir un historique' })
        .selectOption('Soldat');
    await expect(page.locator('.language-choice select')).toHaveCount(0);
    await page.getByRole('button', { name: 'Continuer' }).click();
    await expect(page).toHaveURL(/skills$/);
});
