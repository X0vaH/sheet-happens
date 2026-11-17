import { test, expect } from './helpers/test.js';
import { openSkills, selectSkills } from './helpers/characterCreation.js';
import { listCharacter } from './fixtures/characters.js';

const slotSelect = (page) => page.getByLabel('Emplacement', { exact: true });

async function openEmptySlot(page, position) {
    await page.goto('/');
    await page
        .getByRole('button', { name: `Aller au personnage ${position}` })
        .click();
    await page
        .locator('.carousel-slide')
        .nth(position - 1)
        .getByRole('button', { name: 'Créer un personnage' })
        .click();
    await expect(page).toHaveURL(/\/character\/create$/);
}

async function openSummary(page) {
    await openSkills(page);
    await selectSkills(page);
    await expect(page).toHaveURL(/summary$/);
}

// Steps of a Guerrier after the information step, all already complete in the draft.
const stepsToSummary = [
    'race',
    'class',
    'equipment',
    'abilities',
    'background',
    'skills',
    'summary',
];

test('the empty home slot that was clicked is preselected', async ({
    page,
    api,
}) => {
    api.characters = [listCharacter({ id: 1, name: 'Arthur', orderIndex: 0 })];
    await openEmptySlot(page, 4);

    await expect(slotSelect(page)).toHaveValue('3');
});

test('occupied slots are shown but cannot be chosen', async ({ page, api }) => {
    api.characters = [
        listCharacter({ id: 1, name: 'Arthur', orderIndex: 0 }),
        listCharacter({ id: 2, name: 'Elowen', orderIndex: 2 }),
    ];
    await page.goto('/character/create');

    const options = slotSelect(page).locator('option');
    await expect(options).toHaveText([
        'Emplacement 1 — Arthur (occupé)',
        'Emplacement 2',
        'Emplacement 3 — Elowen (occupé)',
        'Emplacement 4',
        'Emplacement 5',
    ]);
    await expect(options.nth(0)).toBeDisabled();
    await expect(options.nth(2)).toBeDisabled();
    // Without a clicked slot, the first free one is selected.
    await expect(slotSelect(page)).toHaveValue('1');
});

test('the first free slot is sent when the user keeps the default', async ({
    page,
    api,
}) => {
    api.characters = [listCharacter({ id: 1, name: 'Arthur', orderIndex: 0 })];
    await openSummary(page);
    await page.getByRole('button', { name: 'Sauvegarder' }).click();
    await expect(page).toHaveURL('/');

    expect(api.createdCharacters).toHaveLength(1);
    expect(api.createdCharacters[0].order_index).toBe(1);
});

test('a slot changed during creation is sent when saving', async ({
    page,
    api,
}) => {
    api.characters = [listCharacter({ id: 1, name: 'Arthur', orderIndex: 0 })];
    await openSummary(page);
    await page
        .getByRole('navigation', { name: 'Étapes de création du personnage' })
        .getByRole('link', { name: 'Informations' })
        .click();
    await slotSelect(page).selectOption('4');
    for (const step of stepsToSummary) {
        await page.getByRole('button', { name: 'Continuer' }).click();
        await expect(page).toHaveURL(new RegExp(step + '$'));
    }
    await page.getByRole('button', { name: 'Sauvegarder' }).click();
    await expect(page).toHaveURL('/');

    expect(api.createdCharacters[0].order_index).toBe(4);
});

test('the slot chosen is kept when coming back to the first step', async ({
    page,
}) => {
    await openEmptySlot(page, 3);
    await slotSelect(page).selectOption('4');
    await page.getByLabel('Nom du personnage').fill('Arthur');
    await page
        .getByLabel('Alignement', { exact: true })
        .selectOption('Neutre bon');
    await page.getByRole('button', { name: 'Continuer' }).click();
    await expect(page).toHaveURL(/race$/);

    await page.goBack();

    await expect(page).toHaveURL(/\/character\/create$/);
    await expect(slotSelect(page)).toHaveValue('4');
});
