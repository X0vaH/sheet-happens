import { test, expect } from './helpers/test.js';
import { detailCharacter } from './fixtures/characters.js';

test('the sheet shows the character returned by the API', async ({
    page,
    api,
}) => {
    api.characterDetails[1] = detailCharacter();
    await page.goto('/character/1');

    await expect(page.getByRole('heading', { name: 'Arthur' })).toBeVisible();
    await expect(page.getByText('Style de combat : Défense')).toHaveAttribute(
        'title',
        '+1 CA lorsque le personnage porte une armure.',
    );

    const proficientSaves = page.locator('.save-row.is-proficient');
    await expect(proficientSaves).toHaveCount(2);
    await expect(proficientSaves.nth(0)).toContainText('Force');
    await expect(proficientSaves.nth(1)).toContainText('Constitution');

    const athletics = page.locator('.skill-row', { hasText: 'Athlétisme' });
    await expect(athletics.locator('.skill-dot')).toHaveClass(/active/);
    await expect(athletics.locator('.skill-modifier')).toHaveText('Force');
    await expect(athletics.locator('.skill-bonus')).toHaveText('+2');

    const axe = page.locator('.attack-row', { hasText: "Hache d'armes" });
    await expect(axe.locator('.attack-atk')).toHaveText('3');
    await expect(axe.locator('.attack-damage')).toHaveText(
        '1d8 slashing (1d10 à deux mains)',
    );
    const fireBolt = page.locator('.attack-row', { hasText: 'Trait de feu' });
    await expect(fireBolt.locator('.attack-atk')).toHaveText('—');
    await expect(fireBolt.locator('.attack-damage')).toHaveText(
        'Tour de magie, 36 m',
    );
});

test('saving throws and skills display a single sign for each bonus', async ({
    page,
    api,
}) => {
    const character = detailCharacter();
    const bonuses = [-2, 0, 2];
    character.saving_throws = character.saving_throws.map((save, index) => ({
        ...save,
        bonus: bonuses[index % bonuses.length],
    }));
    character.skills = bonuses.map((bonus, index) => ({
        name: `Skill ${index}`,
        ability: 'Force',
        proficient: index === 2,
        bonus,
    }));
    api.characterDetails[1] = character;
    await page.goto('/character/1');

    await expect(page.locator('.save-score-box')).toHaveText([
        '-2',
        '+0',
        '+2',
        '-2',
        '+0',
        '+2',
    ]);
    await expect(page.locator('.skill-bonus')).toHaveText(['-2', '+0', '+2']);
});

test('a negative initiative is not shown as +-1', async ({ page, api }) => {
    api.characterDetails[1] = detailCharacter({ initiative: -1 });
    await page.goto('/character/1');

    const initiative = page.locator('.stat-card', { hasText: 'Initiative' });
    await expect(initiative.locator('strong')).toHaveText('-1');
});

test('empty lists show Aucun', async ({ page, api }) => {
    api.characterDetails[1] = detailCharacter({
        inventory: [],
        spells: [],
        features: [],
        feats: [],
    });
    await page.goto('/character/1');

    await expect(page.locator('.attack-row')).toHaveText(['Aucun']);
    await expect(page.locator('.capacity-row')).toHaveText(['Aucun']);
});

test('an unknown character redirects to the home page', async ({ page }) => {
    await page.goto('/character/99');

    await expect(page).toHaveURL(/127\.0\.0\.1:4173\/$/);
});

test('the shared character client preserves the full API response', async ({
    page,
    api,
}) => {
    const character = {
        id: 42,
        name: 'Personnage complet',
        max_health_points: 30,
        current_health_points: 21,
        alignment: 'Neutre',
        notes: 'Notes conservées',
        detail: { backstory: 'Histoire conservée' },
        levels: [{ character_level: 4 }],
        next_level: 5,
        origin: {
            specie: { name: 'Humain', base_speed: 9 },
            background: { name: 'Sage' },
        },
        classes: [
            {
                class_definition: {
                    name: 'Magicien',
                    saving_throws: [{ name: 'Intelligence' }],
                },
            },
        ],
        abilities: [
            {
                ability_id: 1,
                ability: { name: 'Intelligence' },
                score: 16,
                effective_score: 18,
            },
            {
                ability_id: 2,
                ability: { name: 'Dextérité' },
                score: 8,
                effective_score: 8,
            },
        ],
        ability_increases: [{ ability_id: 1, increase_amount: 2 }],
        skill_proficiencies: [
            {
                skill: { name: 'Arcanes', ability_id: 1 },
                proficiency_level: 'proficient',
            },
        ],
        languages: [{ language: { name: 'Elfique' } }],
        tools: [{ tool: { name: 'Outils de cartographe' } }],
        inventory: [{ item: { name: 'Torche' }, quantity: 6 }],
        features: [
            {
                feature: { name: 'Style de combat' },
                choices: [
                    {
                        feature_option: {
                            name: 'Défense',
                            description: 'Option choisie',
                        },
                    },
                ],
            },
        ],
        feats: [{ feat: { name: 'Don enregistré' } }],
        spells: [
            { spell: { name: 'Lumière', spell_level: 0 } },
            { spell: { name: 'Bouclier', spell_level: 1 } },
        ],
        spell_slots: [{ spell_level: 1, max_slots: 4, expended_slots: 1 }],
    };
    api.characterDetails[42] = character;
    await page.goto('/');
    const raw = await page.evaluate(async () => {
        const { default: client } = await import('/src/api/characterClient.js');
        return client.get(42);
    });
    expect(raw).toEqual(character);
});

test('the scores include the ability increases', async ({ page, api }) => {
    const character = detailCharacter();
    character.abilities[0] = { ...character.abilities[0], effective_score: 17 };
    character.feats = [
        {
            feat: {
                name: 'Ability Score Improvement',
                description: 'Increase abilities.',
            },
        },
        { feat: { name: 'Alert', description: 'Always ready for danger.' } },
    ];
    api.characterDetails[1] = character;
    await page.goto('/character/1');

    const strength = page.locator('.characteristic-box', { hasText: 'Force' });
    await expect(strength.locator('.characteristic-score-box')).toHaveText(
        '17',
    );
    await expect(strength.locator('.characteristic-modifier')).toHaveText('+3');
    const capacities = page.locator('.capacities-panel');
    await expect(capacities).not.toContainText('Ability Score Improvement');
    await expect(capacities).toContainText('Alert : Always ready for danger.');
    await expect(capacities.locator('.capacity-row')).toHaveCount(2);
});

test('ability score improvement alone leaves no capacities to display', async ({
    page,
    api,
}) => {
    api.characterDetails[1] = detailCharacter({
        features: [],
        feats: [
            {
                feat: {
                    name: 'Ability Score Improvement',
                    description: 'Increase abilities.',
                },
            },
        ],
    });
    await page.goto('/character/1');

    await expect(page.locator('.capacity-row')).toHaveText(['Aucun']);
});

test('the upgrade button opens the level up page', async ({ page, api }) => {
    api.characterDetails[1] = detailCharacter();
    await page.goto('/character/1');

    await page.getByRole('button', { name: 'Améliorer' }).click();
    await expect(page).toHaveURL(/\/character\/1\/level-up$/);
});

test('the delete button removes the character after confirmation', async ({
    page,
    api,
}) => {
    api.characterDetails[1] = detailCharacter();
    await page.goto('/character/1');

    page.once('dialog', (dialog) => dialog.accept());
    await page.getByRole('button', { name: 'Supprimer' }).click();

    await expect(page).toHaveURL(/127\.0\.0\.1:4173\/$/);
    expect(api.deletedCharacterIds).toEqual([1]);
});

test('cancelling the confirmation keeps the character', async ({
    page,
    api,
}) => {
    api.characterDetails[1] = detailCharacter();
    await page.goto('/character/1');

    page.once('dialog', (dialog) => dialog.dismiss());
    await page.getByRole('button', { name: 'Supprimer' }).click();

    await expect(page).toHaveURL(/\/character\/1$/);
    expect(api.deletedCharacterIds).toEqual([]);
});

test('a failed deletion shows the API error and stays on the sheet', async ({
    page,
    api,
}) => {
    api.characterDetails[1] = detailCharacter();
    api.deleteResponse = {
        status: 403,
        body: { message: 'Vous ne pouvez pas supprimer ce personnage.' },
    };
    await page.goto('/character/1');

    page.once('dialog', (dialog) => dialog.accept());
    await page.getByRole('button', { name: 'Supprimer' }).click();

    await expect(page.getByRole('alert')).toContainText(
        'Vous ne pouvez pas supprimer ce personnage.',
    );
    await expect(page).toHaveURL(/\/character\/1$/);
    await expect(page.getByRole('button', { name: 'Supprimer' })).toBeEnabled();
});

test('the upgrade button is disabled at the maximum level', async ({
    page,
    api,
}) => {
    api.characterDetails[1] = detailCharacter({ next_level: null });
    await page.goto('/character/1');

    await expect(
        page.getByRole('button', { name: 'Niveau maximum atteint' }),
    ).toBeDisabled();
    await expect(page.getByRole('button', { name: 'Améliorer' })).toHaveCount(
        0,
    );
});
