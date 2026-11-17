import { draftFields } from '../../configs/characterStorage.js';
import { readChosenLanguages } from './characterLanguages.js';
import {
    draftVersion,
    readDraft,
    readDraftArray,
    readDraftObject,
} from './creationDraft.js';
import { equipmentField } from './creationFlow.js';

let cachedVersion = -1;
let cachedSnapshot = null;

// One typed read of everything the creation steps have saved so far. Cached by draft
// version so the several components/hooks that each call this once per render (identity,
// derived stats, abilities, ...) share one object instead of re-parsing the draft every time.
export function readCreationDraft() {
    const version = draftVersion();
    if (cachedSnapshot && cachedVersion === version) return cachedSnapshot;
    const characterClass = readDraft(draftFields.characterClass);
    cachedSnapshot = {
        orderIndex: readDraft(draftFields.orderIndex),
        name: readDraft(draftFields.name),
        alignment: readDraft(draftFields.alignment),
        notes: readDraft(draftFields.notes),
        race: readDraft(draftFields.race),
        characterClass,
        subclass: readDraft(draftFields.subclass),
        featureOptions: readDraftObject(draftFields.featureOptions),
        equipment: readDraftArray(equipmentField(characterClass)),
        scores: readDraftArray(draftFields.abilities),
        abilityMethod: readDraft(draftFields.abilityMethod),
        abilityPool: readDraftArray(draftFields.abilityPool),
        background: readDraft(draftFields.background),
        skills: readDraftArray(draftFields.skills),
        languages: readChosenLanguages(),
    };
    cachedVersion = version;
    return cachedSnapshot;
}
