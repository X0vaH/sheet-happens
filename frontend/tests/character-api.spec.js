import { test, expect, API_URL } from './helpers/test.js';
import { openSkills, selectSkills } from './helpers/characterCreation.js';
import { creationOptions as options } from './fixtures/creationOptions.js';

const nameOf = (list, id) => list.find((entry) => entry.id === id)?.name;
const byName = (list, name) => list.find((entry) => entry.name === name);

async function openSummary(page, characterClass = 'Guerrier') {
    await openSkills(page, characterClass);
    await selectSkills(page);
    if (characterClass === 'Clerc') {
        for (const name of [
            'Lumière',
            'Thaumaturgie',
            'Flamme sacrée',
            'Soins',
            'Bénédiction',
            'Détection de la magie',
            'Sanctuaire',
        ])
            await page.getByRole('button', { name, exact: true }).click();
        await page.getByRole('button', { name: 'Continuer' }).click();
    }
    await expect(page).toHaveURL(/summary$/);
}

test('saving sends the whole level 1 character in one request', async ({
    page,
    api,
}) => {
    await openSummary(page);
    await page.getByRole('button', { name: 'Sauvegarder' }).click();
    await expect(page).toHaveURL('/');
    expect(api.createdCharacters).toHaveLength(1);
    const [saved] = api.createdCharacters;
    const fighter = byName(options.classes, 'Guerrier');
    const human = byName(options.species, 'Humain');
    const soldier = byName(options.backgrounds, 'Soldat');
    expect(saved).toMatchObject({
        name: 'Arthur',
        alignment: 'Neutre bon',
        notes: null,
        species_id: human.id,
        background_id: soldier.id,
        class_id: fighter.id,
        subclass_id: null,
    });
    expect(saved).not.toHaveProperty('max_health_points');
    expect(saved).not.toHaveProperty('current_health_points');
    expect(
        saved.abilities.map(({ ability_id: id }) =>
            nameOf(options.abilities, id),
        ),
    ).toEqual([
        'Force',
        'Dextérité',
        'Constitution',
        'Intelligence',
        'Sagesse',
        'Charisme',
    ]);
    expect(saved.abilities.map(({ score }) => score)).toEqual([
        15, 14, 13, 12, 10, 8,
    ]);
    expect(
        saved.skill_proficiencies.every(
            ({ proficiency_level: level }) => level === 'proficient',
        ),
    ).toBe(true);
    expect(saved.tools).toEqual([
        { tool_id: soldier.tools[0].id, proficiency_level: 'proficient' },
    ]);
    expect(saved.feature_choices.map(({ feature_id: id }) => id)).toEqual(
        [...human.features, ...fighter.level_one.features].map(({ id }) => id),
    );
    expect(saved.spells).toEqual([]);
});

test('a cleric sends its level 1 subclass', async ({ page, api }) => {
    await openSummary(page, 'Clerc');
    await expect(
        page.getByText('Sous-classe : Domaine de la Vie').first(),
    ).toBeVisible();
    await page.getByRole('button', { name: 'Sauvegarder' }).click();
    await expect(page).toHaveURL('/');
    expect(api.createdCharacters[0].subclass_id).toBe(901);
});

test('a wizard keeps the spell granted by its class without sending it twice', async ({
    page,
    api,
}) => {
    await openSkills(page, 'Magicien');
    await selectSkills(page);
    await expect(
        page.getByText('Déjà accordés par la classe : Projectile magique.'),
    ).toBeVisible();
    // Projectile magique is picked again even though it's already granted at level 1: the
    // payload must still list it only once.
    const chosen = [
        'Trait de feu',
        'Prestidigitation',
        'Main du mage',
        'Projectile magique',
        'Bouclier',
        'Sommeil',
        'Armure du mage',
        'Détection de la magie',
        'Charme-personne',
    ];
    for (const name of chosen)
        await page.getByRole('button', { name, exact: true }).click();
    await page.getByRole('button', { name: 'Continuer' }).click();
    await page.getByRole('button', { name: 'Sauvegarder' }).click();
    await expect(page).toHaveURL('/');
    const wizardSpells = byName(options.classes, 'Magicien').spells;
    const expectedIds = chosen
        .map((name) => byName(wizardSpells, name).id)
        .sort((a, b) => a - b);
    const savedIds = api.createdCharacters[0].spells
        .map(({ spell_id: id }) => id)
        .sort((a, b) => a - b);
    expect(savedIds).toEqual(expectedIds);
});

test('validation errors are listed and the player stays on the summary', async ({
    page,
    api,
}) => {
    api.characterResponse = {
        status: 422,
        body: {
            message: 'Données invalides',
            errors: {
                name: ['Le nom est trop long.'],
                abilities: ['Les caractéristiques sont invalides.'],
            },
        },
    };
    await openSummary(page);
    await page.getByRole('button', { name: 'Sauvegarder' }).click();
    const alert = page.getByRole('alert');
    await expect(alert).toContainText('Le nom est trop long.');
    await expect(alert).toContainText('Les caractéristiques sont invalides.');
    await expect(page).toHaveURL(/summary$/);
    await expect(
        page.getByRole('button', { name: 'Sauvegarder' }),
    ).toBeEnabled();
});

test('a 403 from the API shows its message and keeps the player signed in', async ({
    page,
    api,
}) => {
    const message =
        'Vous avez atteint la limite de 5 personnages. Supprimez-en un avant d’en créer un nouveau.';
    api.characterResponse = { status: 403, body: { message } };
    await openSummary(page);
    await page.getByRole('button', { name: 'Sauvegarder' }).click();
    const alert = page.getByRole('alert');
    await expect(alert).toContainText(message);
    await expect(alert).not.toContainText('Votre session a expiré.');
    await expect(page).toHaveURL(/summary$/);
    await expect(
        page.getByRole('button', { name: 'Sauvegarder' }),
    ).toBeEnabled();
});

test('an expired session asks the player to log in again', async ({
    page,
    api,
}) => {
    api.characterResponse = {
        status: 401,
        body: { message: 'Unauthenticated.' },
    };
    await openSummary(page);
    await page.getByRole('button', { name: 'Sauvegarder' }).click();
    await expect(page.getByRole('alert')).toContainText(
        'Votre session a expiré.',
    );
    await expect(
        page.getByRole('link', { name: 'Reconnectez-vous' }),
    ).toHaveAttribute('href', '/login');
});

test('catalog loading keeps creation chrome and a status box', async ({
    page,
}) => {
    let release;
    const pending = new Promise((resolve) => {
        release = resolve;
    });
    await page.route(
        API_URL + '/api/rulesets/current/character-creation-options',
        async (route) => {
            await pending;
            await route.fallback();
        },
    );
    await page.goto('/character/create');
    await expect(page.locator('.creation-background')).toBeVisible();
    await expect(page.locator('#creation-step-menu')).toBeVisible();
    await expect(page.getByRole('status')).toContainText(
        'Chargement des éléments de création',
    );
    release();
    await expect(
        page.getByRole('heading', { name: 'Informations' }),
    ).toBeVisible();
});

test('character limit check keeps creation chrome and a status box', async ({
    page,
}) => {
    let release;
    const pending = new Promise((resolve) => {
        release = resolve;
    });
    await page.route(API_URL + '/api/characters', async (route) => {
        await pending;
        await route.fallback();
    });
    await page.goto('/character/create');
    await expect(page.locator('.creation-background')).toBeVisible();
    await expect(page.locator('#creation-step-menu')).toBeVisible();
    await expect(page.getByRole('status')).toContainText(
        'Chargement des éléments de création',
    );
    release();
    await expect(
        page.getByRole('heading', { name: 'Informations' }),
    ).toBeVisible();
});

test('a catalog loading failure can be retried', async ({ page }) => {
    let available = false;
    await page.route(
        API_URL + '/api/rulesets/current/character-creation-options',
        (route) => (available ? route.fallback() : route.abort()),
    );
    await page.goto('/character/create');
    await expect(page.locator('.creation-background')).toBeVisible();
    await expect(page.getByRole('alert')).toContainText(
        'Impossible de charger les options de création de personnage.',
    );
    available = true;
    await page.getByRole('button', { name: 'Réessayer' }).click();
    await expect(
        page.getByRole('heading', { name: 'Informations' }),
    ).toBeVisible();
});

test('an empty alignment catalog is explained on the information step', async ({
    page,
}) => {
    await page.route(
        API_URL + '/api/rulesets/current/character-creation-options',
        (route) =>
            route.fulfill({
                status: 200,
                headers: {
                    'Access-Control-Allow-Origin': 'http://127.0.0.1:4173',
                    'Access-Control-Allow-Credentials': 'true',
                },
                contentType: 'application/json',
                body: JSON.stringify({
                    options: { ...options, alignments: undefined },
                }),
            }),
    );
    await page.goto('/character/create');
    await expect(page.getByRole('alert')).toContainText(
        'Aucun alignement disponible.',
    );
});
