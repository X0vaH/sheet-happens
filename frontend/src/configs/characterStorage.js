// Preserve these values to keep existing drafts compatible.
export const draftFields = {
    orderIndex: 'order-index',
    name: 'name',
    alignment: 'alignment',
    notes: 'notes',
    race: 'race',
    characterClass: 'class',
    abilities: 'abilities',
    abilityMethod: 'ability-method',
    abilityPool: 'ability-pool',
    skills: 'skills',
    background: 'background',
    language: 'language',
    languages: 'languages',
    subclass: 'subclass',
    featureOptions: 'feature-options',
};

export const equipmentFields = {
    Guerrier: 'fighter-equipment',
    Barbare: 'Barbare-equipment',
    Clerc: 'Clerc-equipment',
    Magicien: 'Magicien-equipment',
    Roublard: 'Roublard-equipment',
};

// Kept per class (rather than one shared field) so switching between spellcasting classes
// during creation doesn't lose either class's picks (see cleric-spells.spec.js).
export const cantripFields = {
    Guerrier: 'fighter-cantrips',
    Barbare: 'Barbare-cantrips',
    Clerc: 'cleric-cantrips',
    Magicien: 'wizard-cantrips',
    Roublard: 'Roublard-cantrips',
};

export const spellFields = {
    Guerrier: 'fighter-spells',
    Barbare: 'Barbare-spells',
    Clerc: 'cleric-spells',
    Magicien: 'wizard-spells',
    Roublard: 'Roublard-spells',
};

const prefix = 'sheet-happens.character-';

export const storageKeys = Object.fromEntries(
    [
        ...Object.values(draftFields),
        ...Object.values(equipmentFields),
        ...Object.values(cantripFields),
        ...Object.values(spellFields),
    ].map((field) => [field, prefix + field]),
);
