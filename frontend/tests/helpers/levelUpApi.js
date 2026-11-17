import { expect } from '@playwright/test';

export async function mockLevelUpApi(
    page,
    characterClass = 'Guerrier',
    targetLevel = 2,
) {
    const names = [
        'Force',
        'Dextérité',
        'Constitution',
        'Intelligence',
        'Sagesse',
        'Charisme',
    ];
    const caster = ['Magicien', 'Clerc'].includes(characterClass);
    const character = {
        id: {
            Guerrier: 'aldric',
            Magicien: 'elowen',
            Barbare: 'skarr',
            Clerc: 'thalia',
            Roublard: 'vex',
        }[characterClass],
        name: 'Personnage API',
        classe: characterClass,
        level: targetLevel - 1,
        hp: 10,
        stats: Object.fromEntries(names.map((name) => [name, 14])),
        skills: [],
        attacks: [],
        savingThrows: [],
        bonus: 2,
        initiative: 2,
        masterySections: {
            capacities: [],
            'Autre maitrises et langues': [],
            Équipement: [],
        },
        spells: { cantrips: [], learned: [], slots: [] },
    };
    const options = {
        next_level: targetLevel,
        hit_die: {
            Guerrier: 10,
            Magicien: 6,
            Barbare: 12,
            Clerc: 8,
            Roublard: 8,
        }[characterClass],
        progression: {
            notes: [],
            ability_points: targetLevel === 4 ? 2 : 0,
            spell_count: characterClass === 'Magicien' ? 2 : 0,
            cantrip_count: caster && targetLevel === 4 ? 1 : 0,
            slots: caster ? [3] : [],
        },
        available_options: {
            abilities: names.map((name, index) => ({
                id: index + 1,
                name,
                score: 14,
            })),
            subclasses:
                (targetLevel === 3 &&
                    ['Guerrier', 'Barbare', 'Roublard'].includes(
                        characterClass,
                    )) ||
                (targetLevel === 2 && characterClass === 'Magicien')
                    ? [
                          {
                              id: 77,
                              name: 'Sous-classe API',
                              description: 'Description API',
                              features: [],
                          },
                      ]
                    : [],
            features: [
                {
                    id: 40,
                    name: 'Capacité API',
                    description: 'Capacité provenant du serveur',
                    choice_count: 0,
                    options: [],
                },
            ],
            feats:
                targetLevel === 4
                    ? [
                          {
                              id: 88,
                              name: 'Ability Score Improvement',
                              category: 'general',
                              ability_increase_count: 2,
                              description:
                                  'Augmentez une caractéristique de 2 ou deux de 1, sans dépasser 20.',
                          },
                          {
                              id: 89,
                              name: 'Sentinelle',
                              category: 'general',
                              ability_increase_count: 0,
                              description:
                                  'Vos réactions entravent les déplacements ennemis.',
                          },
                      ]
                    : [],
            spells: [
                {
                    id: 101,
                    name: 'Sort API A',
                    spell_level: 1,
                    description: 'Description du sort A.',
                },
                {
                    id: 102,
                    name: 'Sort API B',
                    spell_level: 1,
                    description: 'Description du sort B.',
                },
                {
                    id: 103,
                    name: 'Tour API',
                    spell_level: 0,
                    description: 'Description du tour de magie.',
                },
            ],
        },
    };
    const submissions = [];
    await page.route('**/api/user', (route) =>
        route.fulfill({ json: { id: 1, name: 'Test' } }),
    );
    await page.route('**/sanctum/csrf-cookie', (route) =>
        route.fulfill({ status: 204 }),
    );
    await page.route('**/api/characters', (route) =>
        route.fulfill({ json: { characters: [character] } }),
    );
    await page.route('**/api/characters/' + character.id, (route) =>
        route.fulfill({
            json: {
                character: {
                    id: character.id,
                    name: character.name,
                    max_health_points: character.hp,
                    levels: [{ character_level: character.level }],
                    // Même règle que l'API : pas de niveau suivant au niveau maximum (5).
                    next_level:
                        character.level < 5 ? character.level + 1 : null,
                    classes: [{ class_definition: { name: characterClass } }],
                    origin: { specie: { name: 'Humain', base_speed: 9 } },
                    abilities: names.map((name, index) => ({
                        ability_id: index + 1,
                        ability: { name },
                        score: 14,
                        // Comme l'API : score de base + augmentations acquises.
                        effective_score: character.stats[name],
                    })),
                    ability_increases: names.map((name, index) => ({
                        ability_id: index + 1,
                        increase_amount: character.stats[name] - 14,
                    })),
                    skill_proficiencies: [],
                    languages: [],
                    tools: [],
                    features: [],
                    spells: [],
                    spell_slots: [],
                    inventory: [],
                },
            },
        }),
    );
    await page.route(
        '**/api/characters/' + character.id + '/level-up/options',
        (route) => route.fulfill({ json: options }),
    );
    await page.route(
        '**/api/characters/' + character.id + '/level-up',
        async (route) => {
            const payload = route.request().postDataJSON();
            expect(payload.target_level).toBe(targetLevel);
            expect(payload.spell_ids).toHaveLength(
                options.progression.spell_count +
                    options.progression.cantrip_count,
            );
            expect(
                payload.ability_increases.reduce(
                    (sum, a) => sum + a.increase_amount,
                    0,
                ),
            ).toBe(
                targetLevel === 4
                    ? options.available_options.feats.find(
                          (feat) => feat.id === payload.feat_id,
                      )?.ability_increase_count
                    : options.progression.ability_points,
            );
            expect(payload.hit_point_method).toBe('fixed');
            if (targetLevel === 4) {
                expect(
                    options.available_options.feats.map((feat) => feat.id),
                ).toContain(payload.feat_id);
            } else {
                expect(payload.feat_id).toBe(null);
            }
            expect(payload).not.toHaveProperty('subclass_id');
            submissions.push(payload);
            for (const entry of payload.ability_increases)
                character.stats[names[entry.ability_id - 1]] +=
                    entry.increase_amount;
            const constitution = Math.floor(
                (character.stats.Constitution - 10) / 2,
            );
            character.hp +=
                (payload.hit_point_method === 'fixed'
                    ? options.hit_die / 2 + 1
                    : payload.hit_point_roll) +
                constitution +
                (constitution - 2) * character.level;
            character.level = targetLevel;
            await route.fulfill({
                json: { message: 'Montée de niveau appliquée' },
            });
        },
    );
    return { character, options, submissions };
}
