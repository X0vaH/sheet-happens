/* eslint-disable no-unused-vars -- `options` is kept on every accessor so callers already hand over the catalog */

import {
    armorClassRules,
    defaults,
    scoreBounds,
    unarmoredArmorClassBase,
} from '../../configs/characterCreation.js';
import { findByName, names } from './catalog.js';

// Pages read the catalog only through these accessors. Each one takes the catalog
// (`options`) like the helpers in catalog.js and falls back to '' or [] when the API
// omits a field, so a change in the API shape only touches the matching accessor.

export const alignmentNames = (options) => names(options.alignments);

export const isAlignment = (options, name) =>
    !!findByName(options.alignments, name);

export const alignmentDescription = (options, name) =>
    findByName(options.alignments, name)?.description ?? '';

// The API may send a proficiency as a label or as `{ type, label }`; the page shows labels.
export const classProficiencies = (options, characterClass) =>
    (findByName(options.classes, characterClass)?.proficiencies ?? []).map(
        (proficiency) => proficiency.label ?? proficiency,
    );

export const languageDescription = (options, language) =>
    findByName(options.languages, language)?.description ?? '';

// Armor class data: the armor stats come from the catalog items (`item.armor`), only the
// unarmored base AC is a local rule constant.
export const unarmoredArmorClass = (options) => unarmoredArmorClassBase;

// What a class adds to armor class: `armoredBonus` for the chosen fighting style while
// wearing armor, `unarmoredAbilities` (ability abbreviations) added to the unarmored calculation.
export function armorClassEffects(options, characterClass, fightingStyle) {
    const rules = armorClassRules[characterClass];
    return {
        armoredBonus: rules?.armoredStyleBonus?.[fightingStyle] ?? 0,
        unarmoredAbilities: rules?.unarmoredAbilities ?? [],
    };
}

export const proficiencyBonus = (options) => defaults.proficiencyBonus;

export const characterLevel = (options) => defaults.level;

export const scoreBoundsFor = (options) => scoreBounds;
