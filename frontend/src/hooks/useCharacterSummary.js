import { useMemo } from 'react';
import { defaults, summaryTabs } from '../configs/characterCreation.js';
import {
    abilityNames,
    classLevelOne,
    findByName,
    grantedEquipmentOptions,
    names,
} from '../utils/characterCreation/catalog.js';
import { characterLanguages } from '../utils/characterCreation/characterLanguages.js';
import { buildCharacterPayload } from '../utils/characterCreation/characterPayload.js';
import { characterSkills } from '../utils/characterCreation/characterSkills.js';
import { hasSpellStep } from '../utils/characterCreation/creationFlow.js';
import { readCharacterSpells } from '../utils/characterCreation/spellOptions.js';
import { useCreationDraft } from './useCreationDraft.js';
import { useCreationOptions } from './useCreationOptions.js';

// Everything the summary step shows and sends, derived from the finished draft. Only call it
// once every earlier step is complete (see <CreationStep>): it reads the draft unchecked.
export function useCharacterSummary() {
    const options = useCreationOptions();
    const draft = useCreationDraft();
    return useMemo(() => {
        const {
            name,
            race,
            characterClass,
            subclass,
            featureOptions,
            equipment,
            scores,
            skills: chosenSkills,
            background,
            languages: chosenLanguages,
        } = draft;
        const skills = characterSkills(options, background, chosenSkills);
        const levelOne = classLevelOne(options, characterClass);
        const spells = readCharacterSpells(options, characterClass);
        const grantedSpells = names(levelOne.spells);
        const showSpells =
            hasSpellStep(options, characterClass) || grantedSpells.length > 0;
        const { payload, unsaved } = buildCharacterPayload(options, {
            orderIndex: draft.orderIndex,
            name,
            alignment: draft.alignment || defaults.alignment,
            notes: draft.notes,
            race,
            characterClass,
            subclass,
            featureOptions,
            scores,
            skills,
            background,
            languages: chosenLanguages,
            equipment,
            spells: [...spells.cantrips, ...spells.levelOne],
        });

        return {
            payload,
            unsavedChoices: unsaved.spells.map((spell) => 'Sort : ' + spell),
            abilities: abilityNames(options),
            scores,
            skills,
            characterClass,
            subclass,
            featureOptions,
            background,
            species: findByName(options.species, race),
            backgroundProfile: findByName(options.backgrounds, background),
            proficientSaves: names(
                findByName(options.classes, characterClass).saving_throws,
            ),
            levelOne,
            spells,
            grantedSpells,
            tabs: showSpells ? [...summaryTabs, 'Sorts'] : summaryTabs,
            languages: characterLanguages(
                options,
                race,
                background,
                chosenLanguages,
            ),
            equipmentOptions: grantedEquipmentOptions(
                options,
                characterClass,
                equipment,
            ),
        };
    }, [options, draft]);
}
