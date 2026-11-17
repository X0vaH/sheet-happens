import { cantripFields, spellFields } from '../../configs/characterStorage.js';
import {
    classCantrips,
    classLevelOneSpells,
    findByName,
    names,
} from './catalog.js';
import { readDraftArray } from './creationDraft.js';

export function spellChoiceLimits(options, characterClass) {
    const profile = findByName(options.classes, characterClass);
    return {
        cantripLimit: profile?.cantrip_choice_count ?? 0,
        spellLimit: profile?.spell_choice_count ?? 0,
    };
}

// The saved picks the catalog still offers, without duplicates.
export function knownSpells(saved, optionNames) {
    return [...new Set(saved.filter((value) => optionNames.includes(value)))];
}

export function readSpellSelection(field, optionNames) {
    return knownSpells(readDraftArray(field), optionNames);
}

export function readCharacterSpells(options, characterClass) {
    const cantripField = cantripFields[characterClass];
    const spellField = spellFields[characterClass];
    if (!cantripField || !spellField) return { cantrips: [], levelOne: [] };
    return {
        cantrips: readSpellSelection(
            cantripField,
            names(classCantrips(options, characterClass)),
        ),
        levelOne: readSpellSelection(
            spellField,
            names(classLevelOneSpells(options, characterClass)),
        ),
    };
}

export function hasCompleteSpells(options, characterClass) {
    const { cantripLimit, spellLimit } = spellChoiceLimits(
        options,
        characterClass,
    );
    const selected = readCharacterSpells(options, characterClass);
    return (
        selected.cantrips.length === cantripLimit &&
        selected.levelOne.length === spellLimit
    );
}
