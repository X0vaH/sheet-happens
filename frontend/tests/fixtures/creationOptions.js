// Mirrors the Sheet-Happens-Api ruleset seeders (GET /api/rulesets/current/character-creation-options).
let nextId = 1;
const entries = (list, build) =>
    list.map((value) => ({ id: nextId++, ...build(value) }));
const pick = (list, wanted) => list.filter(({ name }) => wanted.includes(name));

const abilities = entries(
    [
        ['Force', 'FOR'],
        ['Dextérité', 'DEX'],
        ['Constitution', 'CON'],
        ['Intelligence', 'INT'],
        ['Sagesse', 'SAG'],
        ['Charisme', 'CHA'],
    ],
    ([name, abbreviation]) => ({ name, abbreviation }),
);
const abilityId = (name) =>
    abilities.find((ability) => ability.name === name).id;

const skills = entries(
    [
        ['Acrobaties', 'Dextérité'],
        ['Arcanes', 'Intelligence'],
        ['Athlétisme', 'Force'],
        ['Discrétion', 'Dextérité'],
        ['Dressage', 'Sagesse'],
        ['Escamotage', 'Dextérité'],
        ['Histoire', 'Intelligence'],
        ['Intimidation', 'Charisme'],
        ['Investigation', 'Intelligence'],
        ['Médecine', 'Sagesse'],
        ['Nature', 'Intelligence'],
        ['Perception', 'Sagesse'],
        ['Perspicacité', 'Sagesse'],
        ['Persuasion', 'Charisme'],
        ['Religion', 'Intelligence'],
        ['Représentation', 'Charisme'],
        ['Survie', 'Sagesse'],
        ['Tromperie', 'Charisme'],
    ],
    ([name, ability]) => ({ name, ability_id: abilityId(ability) }),
);

const languageDescriptions = {
    Abyssal: 'Langue liée aux démons et aux créatures originaires des Abysses.',
    Céleste:
        'Langue ancienne associée aux êtres célestes et aux plans supérieurs.',
    Draconique:
        'Langue ancienne des Dragons, également utilisée par les Drakéides et certains érudits.',
    Profond:
        'Langue étrange associée à certaines créatures mystérieuses venues des profondeurs ou d’ailleurs.',
    Infernal:
        'Langue structurée des Diables et des créatures liées aux Neuf Enfers.',
    Primordial:
        'Langue ancienne associée aux forces élémentaires et aux créatures des plans élémentaires.',
    Sylvestre:
        'Langue des nombreuses créatures féeriques, fortement associée à la Féerie.',
    'Sous-commun':
        'Langue utilisée comme moyen de communication entre différents peuples de l’Outreterre.',
    Commun: 'Langue la plus répandue, utilisée pour communiquer entre les différents peuples.',
    Nain: 'Langue traditionnelle des Nains, au ton robuste et aux sonorités marquées.',
    Elfique:
        'Langue des Elfes, fluide et mélodieuse, possédant une riche tradition littéraire.',
    Géant: 'Langue des Géants et de plusieurs créatures apparentées, aux sonorités puissantes.',
    Gnome: 'Langue des Gnomes, utilisée notamment dans leurs communautés et leurs écrits techniques.',
    Gobelin:
        'Langue utilisée principalement par les Gobelins, Hobgobelins et autres peuples gobelinoïdes.',
    Halfelin:
        'Langue traditionnelle des Halfelins, surtout parlée au sein de leurs communautés.',
    Orc: 'Langue des Orcs, aux sonorités fortes et directes.',
};
const languages = entries(
    [
        'Commun',
        'Draconique',
        'Elfique',
        'Géant',
        'Gnome',
        'Gobelin',
        'Halfelin',
        'Nain',
        'Orc',
        'Abyssal',
        'Céleste',
        'Infernal',
        'Primordial',
        'Sylvestre',
        'Profond',
        'Sous-commun',
    ],
    (name) => ({ name, description: languageDescriptions[name] }),
);
const languageId = (name) =>
    languages.find((language) => language.name === name).id;

const alignments = entries(
    [
        [
            'Loyal bon',
            'Respecte les règles et cherche à faire le bien et à protéger les autres.',
        ],
        [
            'Neutre bon',
            'Cherche avant tout à faire le bien, sans être strictement attaché aux règles.',
        ],
        [
            'Chaotique bon',
            'Fait ce qu’il croit juste, même s’il doit désobéir aux règles ou aux autorités.',
        ],
        [
            'Loyal neutre',
            'Suit un code, des lois ou des traditions, peu importe qu’ils soient bons ou mauvais.',
        ],
        [
            'Neutre',
            'Agit selon les circonstances sans favoriser particulièrement l’ordre, le chaos, le bien ou le mal.',
        ],
        [
            'Chaotique neutre',
            'Privilégie sa liberté personnelle et agit selon ses propres envies plutôt que selon les règles.',
        ],
        [
            'Loyal mauvais',
            'Utilise les règles, la hiérarchie ou un code pour servir ses propres intérêts au détriment des autres.',
        ],
        [
            'Neutre mauvais',
            'Cherche principalement son propre intérêt et n’hésite pas à nuire aux autres pour l’obtenir.',
        ],
        [
            'Chaotique mauvais',
            'Agit égoïstement et violemment sans se soucier des règles ni du bien-être des autres.',
        ],
    ],
    ([name, description]) => ({ name, description }),
);

const tools = entries(
    [
        ['Ustensiles de calligraphe', 'artisan'],
        ['Outils de voleur', 'kit'],
        ['Véhicules terrestres', 'vehicle'],
    ],
    ([name, category]) => ({ name, tool_category: category }),
);

const features = entries(
    [
        'Vision dans le noir',
        'Résilience naine',
        'Ascendance féerique',
        'Transe',
        'Ruse gnome',
        'Chanceux',
        'Brave',
        'Ascendance draconique',
        'Souffle du dragon',
        'Polyvalence',
        'Endurance implacable',
        'Résistance infernale',
        'Legs infernal',
        'Ingéniosité',
        'Rage',
        'Défense sans armure',
        'Incantation',
        'Domaine divin',
        'Style de combat',
        'Récupération au combat',
        'Récupération arcanique',
        'Expertise',
        'Vol furtif',
        'Argot des voleurs',
    ],
    (name) => ({
        name,
        feature_type: 'species',
        description: 'Description de ' + name + '.',
        options: [],
    }),
);

// The Guerrier's "Style de combat" is a class feature with several FeatureOption rows
// (not its own subclass), like the real ruleset seeder.
features.find(({ name }) => name === 'Style de combat').options = entries(
    [
        ['Archerie', "+2 aux jets d'attaque avec les armes à distance."],
        ['Défense', '+1 CA lorsque le personnage porte une armure.'],
        [
            'Duel',
            "+2 dégâts avec une arme de mêlée tenue à une main lorsque l'autre main ne tient pas d'autre arme.",
        ],
        [
            'Combat à deux armes',
            "Ajoutez le modificateur de caractéristique aux dégâts de l'attaque effectuée avec la seconde arme.",
        ],
        [
            'Combat avec arme à deux mains',
            "Permet de relancer certains résultats faibles sur les dés de dégâts d'une arme utilisée à deux mains.",
        ],
        [
            'Protection',
            'Utilisez votre réaction avec un bouclier pour gêner une attaque visant un allié proche.',
        ],
    ],
    ([name, description]) => ({ name, description }),
);

// Species with their fixed languages (Humain/Demi-elfe's extra free choice isn't modeled yet,
// same as the backend).
const species = entries(
    [
        ['Humain', 'medium', 9, ['Ingéniosité'], ['Commun']],
        [
            'Elfe',
            'medium',
            9,
            ['Ascendance féerique', 'Transe'],
            ['Commun', 'Elfique'],
        ],
        [
            'Nain',
            'medium',
            7.5,
            ['Résilience naine', 'Vision dans le noir'],
            ['Commun', 'Nain'],
        ],
        [
            'Halfelin',
            'small',
            7.5,
            ['Chanceux', 'Brave'],
            ['Commun', 'Halfelin'],
        ],
        [
            'Draconien',
            'medium',
            9,
            ['Ascendance draconique', 'Souffle du dragon'],
            ['Commun', 'Draconique'],
        ],
        [
            'Gnome',
            'small',
            7.5,
            ['Ruse gnome', 'Vision dans le noir'],
            ['Commun', 'Gnome'],
        ],
        [
            'Demi-elfe',
            'medium',
            9,
            ['Polyvalence', 'Ascendance féerique'],
            ['Commun', 'Elfique'],
        ],
        [
            'Demi-orc',
            'medium',
            9,
            ['Endurance implacable', 'Vision dans le noir'],
            ['Commun', 'Orc'],
        ],
        [
            'Tieffelin',
            'medium',
            9,
            ['Résistance infernale', 'Legs infernal'],
            ['Commun', 'Infernal'],
        ],
    ],
    ([name, size, speed, traits, languageNames]) => ({
        name,
        size_category: size,
        base_speed: speed,
        description: 'Description ' + name + '.',
        features: pick(features, traits),
        languages: languageNames.map((languageName) => ({
            id: languageId(languageName),
            name: languageName,
        })),
    }),
);

const feats = entries(
    ['Initié à la magie', 'Vigilance', 'Robuste', 'Chanceux'],
    (name) => ({
        name,
        category: 'origin',
        description: 'Description de ' + name + '.',
    }),
);

const backgrounds = entries(
    [
        [
            'Acolyte',
            ['Intelligence', 'Sagesse', 'Charisme'],
            ['Perspicacité', 'Religion'],
            ['Ustensiles de calligraphe'],
            ['Initié à la magie'],
            2,
        ],
        [
            'Soldat',
            ['Force', 'Dextérité', 'Constitution'],
            ['Athlétisme', 'Intimidation'],
            ['Véhicules terrestres'],
            ['Vigilance'],
            0,
        ],
        [
            'Sage',
            ['Intelligence', 'Sagesse', 'Charisme'],
            ['Arcanes', 'Histoire'],
            ['Ustensiles de calligraphe'],
            ['Initié à la magie'],
            2,
        ],
        [
            'Criminel',
            ['Dextérité', 'Constitution', 'Intelligence'],
            ['Discrétion', 'Tromperie'],
            ['Outils de voleur'],
            ['Chanceux'],
            0,
        ],
        [
            'Guide',
            ['Force', 'Dextérité', 'Sagesse'],
            ['Survie', 'Athlétisme'],
            ['Véhicules terrestres'],
            ['Robuste'],
            1,
        ],
    ],
    ([
        name,
        abilityNames,
        skillNames,
        toolNames,
        featNames,
        languageChoiceCount,
    ]) => ({
        name,
        description: 'Description ' + name + '.',
        language_choice_count: languageChoiceCount,
        abilities: pick(abilities, abilityNames),
        skills: pick(skills, skillNames),
        tools: pick(tools, toolNames),
        feats: pick(feats, featNames),
    }),
);

const spell = (name, level) => ({
    id: nextId++,
    name,
    spell_level: level,
    school: 'evocation',
    casting_time: '1 action',
    range: '36 mètres',
    duration: 'Instantanée',
    concentration: false,
    ritual: false,
    description: null,
});
const fireBolt = spell('Trait de feu', 0);
const light = spell('Lumière', 0);
const prestidigitation = spell('Prestidigitation', 0);
const mageHand = spell('Main du mage', 0);
const thaumaturgy = spell('Thaumaturgie', 0);
const magicMissile = spell('Projectile magique', 1);
const shield = spell('Bouclier', 1);
const cureWounds = spell('Soins', 1);
const bless = spell('Bénédiction', 1);
const detectMagic = spell('Détection de la magie', 1);
const sacredFlame = spell('Flamme sacrée', 0);
const sanctuary = spell('Sanctuaire', 1);
const sleep = spell('Sommeil', 1);
const mageArmor = spell('Armure du mage', 1);
const charmPerson = spell('Charme-personne', 1);
// Beyond enough to cover each class's own choice count (SpellSeeder gives Clerc/Magicien a
// real choice, not just an exact fill): Clerc 6 cantrips / 5 spells for 3 / 4 required,
// Magicien 5 cantrips / 8 spells for 3 / 6 required.
const guidance = spell('Assistance', 0);
const resistance = spell('Résistance', 0);
const spareTheDying = spell('Stabilisation', 0);
const command = spell('Injonction', 1);
const rayOfFrost = spell('Rayon de givre', 0);
const message = spell('Message', 0);
const identify = spell('Identification', 1);
const featherFall = spell('Chute de plumes', 1);
const clericSpells = [
    light,
    thaumaturgy,
    sacredFlame,
    guidance,
    resistance,
    cureWounds,
    bless,
    detectMagic,
    sanctuary,
    spareTheDying,
    command,
];
const wizardSpells = [
    fireBolt,
    prestidigitation,
    mageHand,
    rayOfFrost,
    message,
    magicMissile,
    shield,
    detectMagic,
    sleep,
    mageArmor,
    charmPerson,
    identify,
    featherFall,
];

// Mirrors the real catalog's weapons, armor and gear (WeaponFactory / ArmorFactory / ItemSeeder).
const armorStats = {
    Cuir: ['light', 11, null],
    'Cuir clouté': ['light', 12, null],
    'Chemise de mailles': ['medium', 13, 2],
    Cuirasse: ['medium', 14, 2],
    'Demi-plate': ['medium', 15, 2],
    'Cotte de mailles': ['heavy', 16, 0],
    'Armure à lattes': ['heavy', 17, 0],
    Harnois: ['heavy', 18, 0],
    Bouclier: ['shield', 2, null],
};
for (const [name, [category, armorClass, limit]] of Object.entries(
    armorStats,
)) {
    armorStats[name] = {
        armor_category: category,
        armor_class: armorClass,
        dexterity_bonus_limit: limit,
    };
}
const items = entries(
    [
        ['Dague', 'weapon'],
        ['Gourdin', 'weapon'],
        ['Masse d’armes', 'weapon'],
        ['Bâton', 'weapon'],
        ['Arc court', 'weapon'],
        ['Épée longue', 'weapon'],
        ['Épée courte', 'weapon'],
        ['Hache d’armes', 'weapon'],
        ['Rapière', 'weapon'],
        ['Arc long', 'weapon'],
        ['Hachette', 'weapon'],
        ['Arbalète légère', 'weapon'],
        ['Cuir', 'armor'],
        ['Cuir clouté', 'armor'],
        ['Chemise de mailles', 'armor'],
        ['Cuirasse', 'armor'],
        ['Demi-plate', 'armor'],
        ['Cotte de mailles', 'armor'],
        ['Armure à lattes', 'armor'],
        ['Harnois', 'armor'],
        ['Bouclier', 'armor'],
        ['Sac à dos', 'gear'],
        ['Corde en chanvre (15 m)', 'gear'],
        ['Torche', 'gear'],
        ['Rations de voyage (1 jour)', 'consumable'],
        ['Potion de soins', 'consumable'],
        ['Gemme (rubis)', 'treasure'],
        ['Sacoche à composantes', 'gear'],
        ['Outils de voleur', 'tool'],
        ['Flèches (20)', 'gear'],
        ['Carreaux d’arbalète (20)', 'gear'],
        ['Paquetage d’explorateur', 'gear'],
        ['Paquetage d’exploration souterraine', 'gear'],
        ['Symbole sacré', 'gear'],
        ['Grimoire', 'gear'],
    ],
    ([name, type]) => ({
        name,
        item_type: type,
        description: null,
        weight: 1,
        value: 1,
        armor: armorStats[name] ?? null,
    }),
);
const itemByName = (name) => items.find((item) => item.name === name);

// class => list of [group label, list of [option label, {item name: quantity}]]. A group with a
// single option is automatically-granted gear, not a real choice — same as the backend.
function equipmentGroups(groups) {
    return groups.map(([label, options]) => ({
        id: nextId++,
        label,
        options: options.map(([optionLabel, itemQuantities]) => ({
            id: nextId++,
            label: optionLabel,
            items: Object.entries(itemQuantities).map(
                ([itemName, quantity]) => ({
                    ...itemByName(itemName),
                    quantity,
                }),
            ),
        })),
    }));
}

const classes = entries(
    [
        [
            'Barbare',
            12,
            ['Force', 'Constitution'],
            ['Rage', 'Défense sans armure'],
            [],
            [],
            2,
            0,
            0,
            [
                [
                    'Arme',
                    [
                        ['Hache d’armes', { 'Hache d’armes': 1 }],
                        ['Deux hachettes', { Hachette: 2 }],
                    ],
                ],
                [
                    'Équipement d’aventurier',
                    [
                        [
                            'Paquetage d’explorateur',
                            { 'Paquetage d’explorateur': 1 },
                        ],
                    ],
                ],
            ],
            [
                'Armures légères',
                'Armures intermédiaires',
                'Boucliers',
                'Armes courantes',
                'Armes de guerre',
            ],
        ],
        [
            'Clerc',
            8,
            ['Sagesse', 'Charisme'],
            ['Incantation', 'Domaine divin'],
            [],
            [
                {
                    id: 901,
                    name: 'Domaine de la Vie',
                    description: 'Soins et préservation de la vie.',
                },
            ],
            2,
            3,
            4,
            [
                [
                    'Arme',
                    [
                        ['Masse d’armes', { 'Masse d’armes': 1 }],
                        ['Épée longue', { 'Épée longue': 1 }],
                    ],
                ],
                [
                    'Armure',
                    [
                        [
                            'Chemise de mailles et bouclier',
                            { 'Chemise de mailles': 1, Bouclier: 1 },
                        ],
                        ['Cuir clouté', { 'Cuir clouté': 1 }],
                    ],
                ],
                [
                    'Équipement d’aventurier',
                    [
                        [
                            'Paquetage d’explorateur',
                            { 'Paquetage d’explorateur': 1 },
                        ],
                        [
                            'Paquetage d’exploration souterraine',
                            { 'Paquetage d’exploration souterraine': 1 },
                        ],
                    ],
                ],
                ['Symbole sacré', [['Symbole sacré', { 'Symbole sacré': 1 }]]],
            ],
            [
                'Armures légères',
                'Armures intermédiaires',
                'Boucliers',
                'Armes courantes',
            ],
        ],
        [
            'Guerrier',
            10,
            ['Force', 'Constitution'],
            ['Style de combat', 'Récupération au combat'],
            [],
            [],
            2,
            0,
            0,
            [
                [
                    'Armure',
                    [
                        ['Cotte de mailles', { 'Cotte de mailles': 1 }],
                        [
                            'Armure de cuir, arc long et flèches',
                            { Cuir: 1, 'Arc long': 1, 'Flèches (20)': 1 },
                        ],
                    ],
                ],
                [
                    'Arme principale',
                    [
                        [
                            'Épée longue et bouclier',
                            { 'Épée longue': 1, Bouclier: 1 },
                        ],
                        ['Deux épées longues', { 'Épée longue': 2 }],
                    ],
                ],
                [
                    'Arme secondaire',
                    [
                        [
                            'Arbalète légère et carreaux',
                            {
                                'Arbalète légère': 1,
                                'Carreaux d’arbalète (20)': 1,
                            },
                        ],
                        ['Deux hachettes', { Hachette: 2 }],
                    ],
                ],
                [
                    'Équipement d’aventurier',
                    [
                        [
                            'Paquetage d’explorateur',
                            { 'Paquetage d’explorateur': 1 },
                        ],
                        [
                            'Paquetage d’exploration souterraine',
                            { 'Paquetage d’exploration souterraine': 1 },
                        ],
                    ],
                ],
            ],
            [
                'Toutes les armures',
                'Boucliers',
                'Armes courantes',
                'Armes de guerre',
            ],
        ],
        [
            'Magicien',
            6,
            ['Intelligence', 'Sagesse'],
            ['Incantation', 'Récupération arcanique'],
            [magicMissile],
            [],
            2,
            3,
            6,
            [
                [
                    'Arme',
                    [
                        ['Dague', { Dague: 1 }],
                        ['Bâton', { Bâton: 1 }],
                    ],
                ],
                [
                    'Composantes arcaniques',
                    [['Sacoche à composantes', { 'Sacoche à composantes': 1 }]],
                ],
                ['Grimoire', [['Grimoire', { Grimoire: 1 }]]],
            ],
            ['Bâtons', 'Dagues', 'Fléchettes', 'Frondes', 'Arbalètes légères'],
        ],
        [
            'Roublard',
            8,
            ['Dextérité', 'Intelligence'],
            ['Expertise', 'Vol furtif', 'Argot des voleurs'],
            [],
            [],
            4,
            0,
            0,
            [
                [
                    'Arme de mêlée',
                    [
                        ['Rapière', { Rapière: 1 }],
                        ['Épée courte', { 'Épée courte': 1 }],
                    ],
                ],
                [
                    'Arme à distance',
                    [
                        [
                            'Arc court et flèches',
                            { 'Arc court': 1, 'Flèches (20)': 1 },
                        ],
                        ['Deux dagues', { Dague: 2 }],
                    ],
                ],
                [
                    'Équipement d’aventurier',
                    [
                        [
                            'Paquetage d’explorateur',
                            { 'Paquetage d’explorateur': 1 },
                        ],
                        [
                            'Paquetage d’exploration souterraine',
                            { 'Paquetage d’exploration souterraine': 1 },
                        ],
                    ],
                ],
                [
                    'Outils de voleur',
                    [['Outils de voleur', { 'Outils de voleur': 1 }]],
                ],
            ],
            [
                'Armures légères',
                'Armes courantes',
                'Épées longues',
                'Épées courtes',
                'Rapières',
                'Arbalètes de poing',
            ],
        ],
    ],
    ([
        name,
        hitDie,
        saves,
        featureNames,
        levelOneSpells,
        subclasses,
        skillChoiceCount,
        cantripChoiceCount,
        spellChoiceCount,
        startingEquipment,
        proficiencies,
    ]) => ({
        name,
        hit_die: hitDie,
        description: 'Description ' + name + '.',
        saving_throws: pick(abilities, saves),
        proficiencies,
        skill_choice_count: skillChoiceCount,
        cantrip_choice_count: cantripChoiceCount,
        spell_choice_count: spellChoiceCount,
        spells:
            name === 'Clerc'
                ? clericSpells
                : name === 'Magicien'
                  ? wizardSpells
                  : [],
        level_one: {
            features: pick(features, featureNames),
            spells: levelOneSpells,
            subclasses,
        },
        starting_equipment_groups: equipmentGroups(startingEquipment),
    }),
);

export const creationOptions = {
    ruleset: { id: 1, name: 'D&D 5e', version: '2024', edition: '5e' },
    abilities,
    skills,
    languages,
    alignments,
    tools,
    species,
    backgrounds,
    classes,
    feats,
    items,
};
