import { test, expect } from './helpers/test.js';
import { listCharacter } from './fixtures/characters.js';
import { buildSlots } from '../src/utils/characterMapping.js';

const slotNames = (slots) => slots.map((slot) => slot?.name ?? null);

test('buildSlots keeps each character at its order index', () => {
    const slots = buildSlots(
        [
            { name: 'A', orderIndex: 0 },
            { name: 'B', orderIndex: 1 },
            { name: 'C', orderIndex: 4 },
        ],
        5,
    );
    expect(slotNames(slots)).toEqual(['A', 'B', null, null, 'C']);
});

test('buildSlots puts duplicate, out of range and missing indexes in free slots', () => {
    const slots = buildSlots(
        [
            { name: 'A', orderIndex: 2 },
            { name: 'B', orderIndex: 2 },
            { name: 'C', orderIndex: 7 },
            { name: 'D', orderIndex: null },
        ],
        5,
    );
    expect(slotNames(slots)).toEqual(['B', 'C', 'A', 'D', null]);
});

test('home shows five slots with the characters at their order index', async ({
    page,
    api,
}) => {
    api.characters = [
        listCharacter({ id: 1, name: 'Arthur', orderIndex: 0 }),
        listCharacter({ id: 2, name: 'Elowen', orderIndex: 4 }),
    ];
    await page.goto('/');

    const slides = page.locator('.carousel-slide');
    await expect(slides).toHaveCount(5);
    await expect(page.locator('.carousel-dot')).toHaveCount(5);
    await expect(slides.nth(0)).toContainText('Arthur');
    await expect(slides.nth(4)).toContainText('Elowen');
    for (const index of [1, 2, 3])
        await expect(
            slides.nth(index).locator('.character-card-empty'),
        ).toHaveCount(1);
    await expect(page.locator('.character-count-badge')).toHaveText(
        'Mes personnages (2)',
    );
});

test('home does not lose a character with a duplicate order index', async ({
    page,
    api,
}) => {
    api.characters = [
        listCharacter({ id: 1, name: 'Arthur', orderIndex: 0 }),
        listCharacter({ id: 2, name: 'Elowen', orderIndex: 0 }),
    ];
    await page.goto('/');

    await expect(page.locator('.carousel-slide').nth(0)).toContainText(
        'Arthur',
    );
    await expect(page.locator('.carousel-slide').nth(1)).toContainText(
        'Elowen',
    );
});

test('an empty slot only shows a plus icon and opens the creation', async ({
    page,
}) => {
    await page.goto('/');

    await expect(page.locator('.character-card-empty')).toHaveCount(5);
    const emptySlot = page.getByRole('button', { name: 'Créer un personnage' });
    await expect(emptySlot).toHaveText('');
    await expect(emptySlot.locator('svg')).toBeVisible();
    await emptySlot.click();
    await expect(page).toHaveURL(/\/character\/create$/);
});

test('a character with an unknown class uses the fighter image', async ({
    page,
    api,
}) => {
    api.characters = [
        listCharacter({ id: 1, name: 'Arthur', className: 'Paladin' }),
    ];
    await page.goto('/');

    await expect(page.getByAltText('Arthur')).toHaveAttribute(
        'src',
        /guerrier/,
    );
});

test('home shows the API error', async ({ page, api }) => {
    api.charactersResponse = {
        status: 500,
        body: { message: 'Erreur du serveur.' },
    };
    await page.goto('/');

    await expect(page.getByRole('alert')).toHaveText('Erreur du serveur.');
    await expect(page.locator('.carousel-slide')).toHaveCount(0);
});

test('the next evolution comes from the API', async ({ page, api }) => {
    api.characters = [
        listCharacter({ id: 1, name: 'Arthur', orderIndex: 0, level: 2 }),
        listCharacter({ id: 2, name: 'Elowen', orderIndex: 1, level: 5 }),
    ];
    await page.goto('/');

    const slides = page.locator('.carousel-slide');
    await expect(slides.nth(0)).toContainText('Prochaine évolution : Niveau 3');
    await expect(slides.nth(1)).toContainText(
        'Prochaine évolution : Niveau maximum atteint',
    );
});

const fiveCharacters = () =>
    [1, 2, 3, 4, 5].map((id) =>
        listCharacter({ id, name: `Perso ${id}`, orderIndex: id - 1 }),
    );

test('with five characters the creation links are disabled and explained', async ({
    page,
    api,
}) => {
    api.characters = fiveCharacters();
    await page.goto('/');

    await expect(
        page.getByRole('button', { name: /Créer un personnage/ }).first(),
    ).toBeDisabled();
    await expect(
        page.getByText('Limite de 5 personnages atteinte').first(),
    ).toBeVisible();
    await expect(
        page.getByRole('link', { name: 'Créer un personnage' }),
    ).toHaveCount(0);
});

test('with four characters the creation link still works', async ({
    page,
    api,
}) => {
    api.characters = fiveCharacters().slice(0, 4);
    await page.goto('/');

    await expect(
        page.getByRole('link', { name: 'Créer un personnage' }),
    ).toBeVisible();
    await expect(
        page.getByText('Limite de 5 personnages atteinte'),
    ).toHaveCount(0);
});

test('opening the creation URL with five characters shows the limit instead of the form', async ({
    page,
    api,
}) => {
    api.characters = fiveCharacters();
    await page.goto('/character/create');

    await expect(page.getByRole('alert')).toContainText(
        'Limite de 5 personnages atteinte',
    );
    await expect(
        page.getByRole('heading', { name: 'Informations' }),
    ).toHaveCount(0);
    await page.getByRole('link', { name: /Retour à l/ }).click();
    await expect(page).toHaveURL(/\/$/);
});

test('the character count is requested once on the creation page', async ({
    page,
    api,
}) => {
    api.characters = fiveCharacters().slice(0, 4);
    let listRequests = 0;
    page.on('request', (request) => {
        if (
            request.method() === 'GET' &&
            new URL(request.url()).pathname === '/api/characters'
        )
            listRequests += 1;
    });
    await page.goto('/character/create');

    await expect(
        page.getByRole('heading', { name: 'Informations' }),
    ).toBeVisible();
    expect(listRequests).toBe(1);
});

test('the disabled creation button is described by the limit message', async ({
    page,
    api,
}) => {
    api.characters = fiveCharacters();
    await page.goto('/');

    const button = page.getByRole('button', { name: /Créer un personnage/ });
    await expect(button.first()).toBeDisabled();
    await expect(button.first()).toHaveAccessibleDescription(
        'Limite de 5 personnages atteinte',
    );
});

test('the creation link follows the character list when returning home', async ({
    page,
    api,
}) => {
    api.characters = fiveCharacters().slice(0, 4);
    await page.goto('/');
    await expect(
        page.getByRole('link', { name: 'Créer un personnage' }),
    ).toBeVisible();

    await page.locator('.navbar-user').click();
    await expect(page).toHaveURL(new RegExp('/settings$'));
    api.characters = fiveCharacters();
    await page.locator('.navbar-link', { hasText: 'Accueil' }).click();
    await expect(page).toHaveURL(new RegExp('/$'));
    await expect(
        page.getByRole('button', { name: /Créer un personnage/ }).first(),
    ).toBeDisabled();
});
