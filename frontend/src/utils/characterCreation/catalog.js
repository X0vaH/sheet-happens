// Helpers over the catalog returned by GET /api/rulesets/current/character-creation-options.
// Drafts keep catalog names (unique per ruleset); ids are resolved when the character is sent.

export const names = (list = []) => list.map((entry) => entry.name);

export const findByName = (list = [], name) =>
    list.find((entry) => entry.name === name);

// Step pages store scores as arrays ordered like `options.abilities` (stable, sorted by id:
// FOR, DEX, CON, INT, SAG, CHA — the order saved drafts were written in).
export const abilityNames = (options) => names(options.abilities);

export function abilityIndex(options, abilityId) {
    return options.abilities.findIndex((ability) => ability.id === abilityId);
}

// Position of an ability in score arrays, by its catalog abbreviation (FOR, DEX, CON...).
export function abilityIndexByAbbreviation(options, abbreviation) {
    return options.abilities.findIndex(
        (ability) => ability.abbreviation === abbreviation,
    );
}

// Positions of the abilities that derived stats (AC, initiative, hit points) depend on.
export const derivedAbilityIndexes = (options) => ({
    dexterity: abilityIndexByAbbreviation(options, 'DEX'),
    constitution: abilityIndexByAbbreviation(options, 'CON'),
});

export function skillEntries(options) {
    return options.skills.map((skill) => [
        skill.name,
        abilityIndex(options, skill.ability_id),
    ]);
}

export function classLevelOne(options, characterClass) {
    return (
        findByName(options.classes, characterClass)?.level_one ?? {
            features: [],
            spells: [],
            subclasses: [],
        }
    );
}

export function choiceFeatures(options, characterClass) {
    return classLevelOne(options, characterClass).features.filter(
        (feature) => feature.options?.length > 0,
    );
}

// Resolves the option chosen (via ChooseClass's featureOptions map) for a level 1 feature by
// name, e.g. the Guerrier's "Style de combat" — used where the choice affects a derived stat.
export function chosenFeatureOptionName(
    options,
    characterClass,
    featureOptions,
    featureName,
) {
    const feature = classLevelOne(options, characterClass).features.find(
        (entry) => entry.name === featureName,
    );
    return (
        feature?.options?.find(
            (option) => option.id === featureOptions[feature.id],
        )?.name ?? ''
    );
}

// An equipment item with its quantity when above one, e.g. "Javelot × 4".
export const itemLabel = (item) =>
    item.name + (item.quantity > 1 ? ` × ${item.quantity}` : '');

export const sizeLabels = {
    tiny: 'Très petite',
    small: 'Petite',
    medium: 'Moyenne',
    large: 'Grande',
};

export function startingEquipmentGroups(options, characterClass) {
    return (
        findByName(options.classes, characterClass)
            ?.starting_equipment_groups ?? []
    );
}

// A group with a single option isn't a real choice: it's automatically-granted gear
// (see the backend's StartingEquipmentGroup docblock) — no separate mechanism for it.
export function automaticEquipmentGroups(options, characterClass) {
    return startingEquipmentGroups(options, characterClass).filter(
        (group) => group.options.length === 1,
    );
}

export function equipmentChoiceGroups(options, characterClass) {
    return startingEquipmentGroups(options, characterClass).filter(
        (group) => group.options.length > 1,
    );
}

// The options actually granted: every automatic group's only option, plus whichever option
// was picked (by id) for each choice group. `equipment` is index-aligned with equipmentChoiceGroups.
export function grantedEquipmentOptions(
    options,
    characterClass,
    equipment = [],
) {
    return [
        ...automaticEquipmentGroups(options, characterClass).map(
            (group) => group.options[0],
        ),
        ...equipmentChoiceGroups(options, characterClass)
            .map((group, index) =>
                group.options.find((option) => option.id === equipment[index]),
            )
            .filter(Boolean),
    ];
}

export function grantedEquipmentItems(options, characterClass, equipment = []) {
    return grantedEquipmentOptions(options, characterClass, equipment).flatMap(
        (option) => option.items,
    );
}

export function classCantrips(options, characterClass) {
    return (findByName(options.classes, characterClass)?.spells ?? []).filter(
        (spell) => spell.spell_level === 0,
    );
}

export function classLevelOneSpells(options, characterClass) {
    return (findByName(options.classes, characterClass)?.spells ?? []).filter(
        (spell) => spell.spell_level > 0,
    );
}
