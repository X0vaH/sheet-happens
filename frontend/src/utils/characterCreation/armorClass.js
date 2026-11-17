import { scoreBounds } from '../../configs/characterCreation.js';
import { abilityIndexByAbbreviation } from './catalog.js';
import { armorClassEffects, unarmoredArmorClass } from './catalogExtras.js';

function abilityModifier(score) {
    if (
        !Number.isInteger(score) ||
        score < scoreBounds.min ||
        score > scoreBounds.max
    ) {
        return null;
    }
    return Math.floor((score - 10) / 2);
}

// `items` are the catalog items granted to the character; their `armor` stats
// (armor_category, armor_class, dexterity_bonus_limit) drive the computation.
// dexterity_bonus_limit: null = uncapped, 0 = no DEX bonus, n = capped at +n.
// A shield's armor_class is a bonus to AC, not a base AC.
// `scores` follows the catalog's ability order; positions are resolved by abbreviation.
export function calculateArmorClass(
    options,
    characterClass,
    items = [],
    scores = [],
    fightingStyle = '',
) {
    const modifierOf = (abbreviation) =>
        abilityModifier(
            scores[abilityIndexByAbbreviation(options, abbreviation)],
        );
    const stats = items.map((item) => item.armor).filter(Boolean);
    const shield = stats
        .filter(({ armor_category: category }) => category === 'shield')
        .reduce((total, { armor_class: bonus }) => total + (bonus ?? 0), 0);
    const dexterity = modifierOf('DEX');
    const effects = armorClassEffects(options, characterClass, fightingStyle);

    const armor = stats.find(
        ({ armor_category: category }) => category !== 'shield',
    );
    if (armor) {
        const base = (armor.armor_class ?? 0) + shield + effects.armoredBonus;
        if (armor.dexterity_bonus_limit === 0) return base;
        if (dexterity === null) return null;
        return (
            base + Math.min(dexterity, armor.dexterity_bonus_limit ?? Infinity)
        );
    }

    if (dexterity === null) return null;
    let armorClass = unarmoredArmorClass(options) + dexterity;
    for (const abbreviation of effects.unarmoredAbilities) {
        const modifier = modifierOf(abbreviation);
        if (modifier === null) return null;
        armorClass += modifier;
    }
    return armorClass + shield;
}
