// Personnages au format renvoyé par l'API (GET /api/characters et /api/characters/{id}).

const abilityNames = [
    'Force',
    'Dextérité',
    'Constitution',
    'Intelligence',
    'Sagesse',
    'Charisme',
];

// Personnage de la liste de l'accueil (GET /api/characters).
export function listCharacter({
    id,
    name,
    className = 'Guerrier',
    orderIndex = id - 1,
    fightingStyle = null,
    level = 1,
}) {
    return {
        id,
        name,
        alignment: 'Neutre bon',
        order_index: orderIndex,
        fighting_style: fightingStyle,
        origin: {
            specie: { name: 'Humain', base_speed: 30 },
            background: { name: 'Soldat' },
        },
        classes: [{ class_definition: { name: className }, subclass: null }],
        levels: [{ character_level: level }],
        next_level: level < 5 ? level + 1 : null,
    };
}

// Fiche complète d'un Guerrier (GET /api/characters/{id}).
export function detailCharacter(overrides = {}) {
    return {
        ...listCharacter({
            id: 1,
            name: 'Arthur',
            fightingStyle: {
                name: 'Défense',
                description: '+1 CA lorsque le personnage porte une armure.',
            },
        }),
        max_health_points: 12,
        armor_class: 15,
        initiative: 2,
        proficiency_bonus: 2,
        abilities: abilityNames.map((name, index) => ({
            score: 10 + index,
            effective_score: 10 + index,
            ability: { id: index + 1, name },
        })),
        saving_throws: abilityNames.map((ability) => ({
            ability,
            proficient: ability === 'Force' || ability === 'Constitution',
            bonus: 2,
        })),
        skills: [
            {
                name: 'Athlétisme',
                ability: 'Force',
                proficient: true,
                bonus: 2,
            },
            {
                name: 'Arcanes',
                ability: 'Intelligence',
                proficient: false,
                bonus: 2,
            },
        ],
        inventory: [
            {
                quantity: 1,
                item: {
                    name: "Hache d'armes",
                    description: 'Arme : Hache d’armes.',
                    weapon: {
                        damage_dice: '1d8',
                        damage_type: 'slashing',
                        versatile_damage_dice: '1d10',
                        range_normal: null,
                        range_long: null,
                        attack_bonus: 3,
                    },
                },
            },
        ],
        spells: [
            { spell: { name: 'Trait de feu', spell_level: 0, range: '36 m' } },
        ],
        languages: [{ language: { name: 'Commun' } }],
        tools: [],
        features: [
            {
                feature: {
                    name: 'Style de combat',
                    description:
                        'Vous adoptez un style de combat particulier comme spécialité.',
                },
            },
        ],
        feats: [],
        ...overrides,
    };
}
