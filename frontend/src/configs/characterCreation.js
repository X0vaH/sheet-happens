// Données du parcours de création qui ne sont pas (encore) exposées par le catalogue de
// l'API : constantes de règle (jets standard, bornes de saisie), navigation de l'assistant.

// CA sans armure : constante de règle (les stats d'armure viennent du catalogue).
export const unarmoredArmorClassBase = 10;

export const defaults = {
    alignment: '',
    race: '',
    characterClass: '',
    spellClass: 'Magicien',
    level: 1,
    proficiencyBonus: 2,
    abilities: ['', '', '', '', '', ''],
    abilityMethod: '',
};
export const abilityMethods = ['Manuel', 'Dés', 'Standard'];
export const standardAbilityScores = [15, 14, 13, 12, 10, 8];
export const scoreBounds = { min: 3, max: 20 };

// Class rules that change armor class, keyed by class name: a fighting style bonus that
// applies while wearing armor, and abilities (catalog abbreviations) added to the unarmored
// base (10 + DEX).
export const armorClassRules = {
    Guerrier: { armoredStyleBonus: { Défense: 1 } },
    Barbare: { unarmoredAbilities: ['CON'] },
};

export const summaryTabs = [
    'Compétences et maîtrises',
    'Équipement',
    'Capacités et traits',
    'Historique',
    'Langues',
];
