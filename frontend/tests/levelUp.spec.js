import { test, expect } from '@playwright/test';
import { mockLevelUpApi } from './helpers/levelUpApi.js';

test.describe.configure({ mode: 'parallel' });
const examples = {
    Guerrier: 'aldric',
    Magicien: 'elowen',
    Barbare: 'skarr',
    Clerc: 'thalia',
    Roublard: 'vex',
};

async function openLevelUp(page, name = 'Guerrier', level = 2) {
    const api = await mockLevelUpApi(page, name, level);
    await page.goto(`/character/${examples[name]}/level-up`);
    await expect(page.locator('.level-up-character-name')).toHaveText(
        'Personnage API',
    );
    await expect(page.locator('.level-up-number')).toHaveText(
        `Niveau ${level}`,
    );
    return api;
}

async function completeChoices(page) {
    const strength = page.getByLabel('Augmentation de Force', { exact: true });
    if (await strength.count()) {
        await strength.selectOption('2');
    }
    const spells = page.locator('.level-up-spell-options button[aria-pressed]');
    if (await spells.count()) {
        await spells.first().click();
        if (
            await page
                .getByText('Choisissez 2 sorts à ajouter au grimoire')
                .count()
        )
            await spells.nth(1).click();
        const cantrips = page.getByRole('button', {
            name: 'Tours de magie',
            exact: true,
        });
        if (await cantrips.count()) {
            await cantrips.click();
            await spells.first().click();
        }
    }
}

test('level up spell and cantrip descriptions use the information popup', async ({
    page,
}) => {
    await openLevelUp(page, 'Magicien', 4);
    const choices = page.locator('.level-up-spell-options');
    await expect(choices).not.toContainText('Description du sort A.');
    await choices.locator('button[aria-pressed]').first().hover();
    await expect(page.getByRole('tooltip')).toContainText(
        'Description du sort A.',
    );
    await page.keyboard.press('Escape');
    await page
        .getByRole('button', { name: 'Tours de magie', exact: true })
        .click();
    await expect(choices).not.toContainText('Description du tour de magie.');
    await choices.locator('button[aria-pressed]').first().hover();
    await expect(page.getByRole('tooltip')).toContainText(
        'Description du tour de magie.',
    );
});

for (const name of Object.keys(examples)) {
    for (const level of [2, 3, 4, 5]) {
        test(`${name} reaches level ${level} from its direct URL`, async ({
            page,
        }) => {
            const { submissions, character, options } = await openLevelUp(
                page,
                name,
                level,
            );
            if (options.available_options.subclasses.length) {
                await expect(
                    page.getByText('Vous obtenez maintenant la sous-classe'),
                ).toBeVisible();
                await expect(
                    page.getByText('Sous-classe API', { exact: true }),
                ).toBeVisible();
            }
            await expect(
                page.getByRole('button', { name: 'Lancer le dé' }),
            ).toHaveCount(0);
            await completeChoices(page);
            await page
                .getByRole('button', {
                    name: level === 5 ? 'Sauvegarder' : 'Continuer',
                    exact: true,
                })
                .click();
            const dialog = page.getByRole('dialog');
            await expect(dialog).toContainText(
                `niveau ${level - 1} → ${level}`,
            );
            await dialog
                .getByRole('button', { name: 'Confirmer', exact: true })
                .click();
            await expect(page).toHaveURL(`/character/${examples[name]}`);
            expect(submissions).toHaveLength(1);
            expect(character.level).toBe(level);
        });
    }
}

test('after a level up the sheet shows the new level and the increased score', async ({
    page,
}) => {
    await openLevelUp(page, 'Guerrier', 4);
    await page.getByLabel('Augmentation de Force').selectOption('2');
    await page.getByRole('button', { name: 'Continuer', exact: true }).click();
    await page.getByRole('button', { name: 'Confirmer', exact: true }).click();
    await expect(page).toHaveURL('/character/aldric');

    await expect(page.locator('.sheet-level-badge strong')).toHaveText('4');
    const strength = page.locator('.characteristic-box', { hasText: 'Force' });
    await expect(strength.locator('.characteristic-score-box')).toHaveText(
        '16',
    );
    await expect(strength.locator('.characteristic-modifier')).toHaveText('+3');
    await expect(
        page.getByRole('button', { name: 'Améliorer', exact: true }),
    ).toBeEnabled();
});

test('ASI shows ability choices; Constitution updates the heading and all prior levels', async ({
    page,
}) => {
    const { submissions } = await openLevelUp(page, 'Guerrier', 4);
    const proceed = page.getByRole('button', {
        name: 'Continuer',
        exact: true,
    });
    await expect(proceed).toBeDisabled();
    await expect(page.getByLabel('Augmentation de Constitution')).toBeVisible();
    await expect(
        page.getByRole('button', { name: /Ability Score Improvement/ }),
    ).toHaveCount(0);
    await expect(page.locator('.level-up-health')).toHaveCount(0);
    await expect(page.locator('.level-up-heading')).toContainText(
        'Augmentation des points de vie : 10 → 18',
    );
    await page.getByLabel('Augmentation de Constitution').selectOption('2');
    await expect(page.locator('.level-up-heading')).toContainText(
        'Augmentation des points de vie : 10 → 22',
    );
    await expect(
        page.getByLabel('Augmentation de Force').locator('option[value="1"]'),
    ).toHaveAttribute('disabled', '');
    await expect(proceed).toBeEnabled();
    await proceed.click();
    await expect(page.getByRole('dialog')).toContainText(
        'Points de vie : 10 → 22',
    );
    await page.getByRole('button', { name: 'Confirmer', exact: true }).click();
    await expect(page).toHaveURL('/character/aldric');
    expect(submissions[0].feat_id).toBe(88);
});

test('choosing a 2014 feat clears ASI increases and saves the selected feat', async ({
    page,
}) => {
    const { submissions } = await openLevelUp(page, 'Guerrier', 4);
    await page.getByLabel('Augmentation de Constitution').selectOption('2');
    await page.getByLabel('Choix du niveau 4').selectOption('89');
    await expect(page.getByLabel('Augmentation de Constitution')).toHaveCount(
        0,
    );
    await expect(page.locator('.level-up-heading')).toContainText('10 → 18');
    await page.getByRole('button', { name: 'Continuer', exact: true }).click();
    await expect(page.getByRole('dialog')).toContainText('Don : Sentinelle');
    await page.getByRole('button', { name: 'Confirmer', exact: true }).click();
    await expect(page).toHaveURL('/character/aldric');
    expect(submissions[0].feat_id).toBe(89);
    expect(submissions[0].ability_increases).toEqual([]);
});

test('switching back to ASI requires allocating its two points again', async ({
    page,
}) => {
    await openLevelUp(page, 'Guerrier', 4);
    await page.getByLabel('Augmentation de Constitution').selectOption('2');
    await page.getByLabel('Choix du niveau 4').selectOption('89');
    await page.getByLabel('Choix du niveau 4').selectOption('88');
    await expect(page.getByLabel('Augmentation de Constitution')).toHaveValue(
        '0',
    );
    await expect(
        page.getByRole('button', { name: 'Continuer', exact: true }),
    ).toHaveAttribute('aria-disabled', 'true');
});

test('feature options require the exact count and are included in confirmation and payload', async ({
    page,
}) => {
    const api = await mockLevelUpApi(page, 'Guerrier', 3);
    api.options.available_options.features = [
        {
            id: 50,
            name: 'Manœuvres',
            description: 'Choisissez deux manœuvres.',
            choice_count: 2,
            options: [1, 2, 3].map((id) => ({
                id,
                name: `Manœuvre ${id}`,
                description: `Description ${id}`,
            })),
        },
    ];
    await page.goto('/character/aldric/level-up');
    const proceed = page.getByRole('button', {
        name: 'Continuer',
        exact: true,
    });
    await expect(proceed).toBeDisabled();
    await page.getByRole('button', { name: 'Manœuvre 1', exact: true }).click();
    await expect(proceed).toBeDisabled();
    await page.getByRole('button', { name: 'Manœuvre 2', exact: true }).click();
    await expect(
        page.getByRole('button', { name: 'Manœuvre 3', exact: true }),
    ).toBeDisabled();
    await proceed.click();
    await expect(page.getByRole('dialog')).toContainText(
        'Manœuvre 1, Manœuvre 2',
    );
    await page.getByRole('button', { name: 'Confirmer', exact: true }).click();
    await expect(page).toHaveURL('/character/aldric');
    expect(api.submissions[0].feature_choices).toEqual([
        { feature_id: 50, option_ids: [1, 2] },
    ]);
});

test('failed save preserves choices and allows retry without duplicate submission', async ({
    page,
}) => {
    const { submissions } = await openLevelUp(page, 'Magicien', 4);
    await completeChoices(page);
    const url = '**/api/characters/elowen/level-up';
    const fail = (route) =>
        route.fulfill({
            status: 422,
            json: { errors: { spell_ids: ['Choix de sorts refusé.'] } },
        });
    await page.route(url, fail);
    await page.getByRole('button', { name: 'Continuer', exact: true }).click();
    await page.getByRole('button', { name: 'Confirmer', exact: true }).click();
    await expect(page.getByRole('alert')).toHaveText('Choix de sorts refusé.');
    await expect(page.getByRole('dialog')).toContainText(
        'Sort API A, Sort API B',
    );
    expect(submissions).toHaveLength(0);
    await page.unroute(url, fail);
    await page
        .getByRole('button', { name: 'Confirmer', exact: true })
        .dblclick();
    await expect(page).toHaveURL('/character/elowen');
    expect(submissions).toHaveLength(1);
});

test('cancel closes confirmation; return leaves without saving', async ({
    page,
}) => {
    const { submissions } = await openLevelUp(page);
    await page.getByRole('button', { name: 'Continuer', exact: true }).click();
    await page.getByRole('button', { name: 'Annuler', exact: true }).click();
    await expect(page.getByRole('dialog')).toHaveCount(0);
    await expect(page).toHaveURL('/character/aldric/level-up');
    expect(submissions).toHaveLength(0);
    await page.getByRole('link', { name: 'Retour', exact: true }).click();
    await expect(page).toHaveURL('/character/aldric');
    expect(submissions).toHaveLength(0);
});

test('maximum level displays an explanation and a return link without fetching options', async ({
    page,
}) => {
    const api = await mockLevelUpApi(page, 'Barbare', 5);
    api.character.level = 5;
    let requests = 0;
    page.on('request', (request) => {
        if (request.url().endsWith('/level-up/options')) requests++;
    });
    await page.goto('/character/skarr/level-up');
    await expect(
        page.getByRole('heading', { name: 'Niveau maximum atteint' }),
    ).toBeVisible();
    expect(requests).toBe(0);
    await page.getByRole('link', { name: 'Retour à la fiche' }).click();
    await expect(page).toHaveURL('/character/skarr');
});

for (const width of [1920, 768, 390, 320]) {
    test(`level up layout at ${width}px`, async ({ page }, testInfo) => {
        await page.setViewportSize({ width, height: 1080 });
        await openLevelUp(page, 'Magicien', 4);
        await expect(
            page.getByLabel('Augmentation de Force', { exact: true }),
        ).toBeVisible();
        await page.evaluate(() => document.fonts.ready);
        expect(
            await page.evaluate(
                () => document.documentElement.scrollWidth <= innerWidth,
            ),
        ).toBe(true);
        await expect(
            page.locator('.level-up-heading .level-up-hit-points'),
        ).toBeVisible();
        await page.screenshot({
            path: testInfo.outputPath('level-up.png'),
            fullPage: true,
        });
    });
}

test('loading uses a centered box before options arrive', async ({ page }) => {
    await mockLevelUpApi(page);
    let release;
    const pending = new Promise((resolve) => {
        release = resolve;
    });
    await page.route(
        '**/api/characters/aldric/level-up/options',
        async (route) => {
            await pending;
            await route.fallback();
        },
    );
    await page.goto('/character/aldric/level-up');
    await expect(
        page.locator('.level-up-loading .creation-options-status'),
    ).toContainText('Chargement des choix');
    release();
    await expect(page.locator('.level-up-character-name')).toHaveText(
        'Personnage API',
    );
});

test('empty validation errors show a load error instead of an endless spinner', async ({
    page,
}) => {
    await mockLevelUpApi(page);
    await page.route('**/api/characters/aldric/level-up/options', (route) =>
        route.fulfill({
            status: 500,
            json: { errors: {}, message: 'Chargement impossible.' },
        }),
    );
    await page.goto('/character/aldric/level-up');
    await expect(page.getByRole('alert')).toHaveText('Chargement impossible.');
    await expect(page.getByRole('status')).toHaveCount(0);
});

test('missing optional lists do not crash level up', async ({ page }) => {
    const { options } = await mockLevelUpApi(page);
    for (const key of ['features', 'subclasses', 'spells'])
        delete options.available_options[key];
    await page.goto('/character/aldric/level-up');
    await expect(
        page.getByRole('button', { name: 'Continuer', exact: true }),
    ).toBeEnabled();
});

test('ability ids survive renamed labels and a one-point feat limits choices', async ({
    page,
}) => {
    const { options, submissions } = await mockLevelUpApi(page, 'Guerrier', 4);
    options.available_options.abilities[0].name = 'Force renommée';
    options.available_options.feats[0].id = 0;
    options.available_options.feats[0].ability_increase_count = 1;
    await page.goto('/character/aldric/level-up');
    const strength = page.getByLabel('Augmentation de Force renommée');
    await expect(strength.locator('option[value="2"]')).toHaveCount(0);
    await strength.selectOption('1');
    await expect(
        page
            .getByLabel('Augmentation de Constitution')
            .locator('option[value="1"]'),
    ).toHaveAttribute('disabled', '');
    await page.getByRole('button', { name: 'Continuer', exact: true }).click();
    await page.getByRole('button', { name: 'Confirmer', exact: true }).click();
    await expect(page).toHaveURL('/character/aldric');
    expect(submissions[0].feat_id).toBe(0);
    expect(submissions[0].ability_increases).toEqual([
        { ability_id: 1, increase_amount: 1 },
    ]);
});

test('choosing a feat moves spell slots to the column without ability choices', async ({
    page,
}) => {
    await openLevelUp(page, 'Magicien', 4);
    await page.getByLabel('Choix du niveau 4').selectOption('89');
    await expect(
        page
            .locator('.level-up-column')
            .nth(1)
            .getByLabel('Emplacements de sorts'),
    ).toBeVisible();
    await expect(
        page
            .locator('.level-up-column')
            .first()
            .getByLabel('Emplacements de sorts'),
    ).toHaveCount(0);
});

test('low Constitution preview applies the minimum separately to prior levels', async ({
    page,
}) => {
    const { character, options } = await mockLevelUpApi(page, 'Magicien', 4);
    character.hp = 3;
    character.stats.Constitution = 1;
    options.available_options.abilities[2].score = 1;
    await page.goto('/character/elowen/level-up');
    await page.getByLabel('Augmentation de Constitution').selectOption('2');
    await expect(page.locator('.level-up-heading')).toContainText('3 → 5');
});
