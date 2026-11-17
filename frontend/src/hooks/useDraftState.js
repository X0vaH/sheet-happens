import { useEffect, useRef, useState } from 'react';
import {
    cantripFields,
    draftFields,
    equipmentFields,
    spellFields,
} from '../configs/characterStorage.js';
import {
    readDraft,
    readDraftArray,
    readDraftObject,
    saveDraft,
} from '../utils/characterCreation/creationDraft.js';

function persist(field, value) {
    saveDraft(field, typeof value === 'string' ? value : JSON.stringify(value));
}

// useState whose setter also writes the new value to the creation draft
// (strings as they are, everything else as JSON). The initial value is written too, so
// the draft always holds what the step shows even if the user never changes it.
export function useDraftState(field, initial) {
    const [value, setValue] = useState(initial);
    useEffect(() => {
        persist(field, value);
        // eslint-disable-next-line react-hooks/exhaustive-deps -- only the first value is written on mount
    }, []);
    function update(next) {
        setValue(next);
        persist(field, next);
    }
    return [value, update];
}

// The hooks below take a key of `draftFields`, so a step names what it edits and never
// touches storage fields. Each starts from what the draft holds and returns
// `[value, setValue]` like `useDraftState`.

// A free text value.
export function useDraftText(key) {
    return useDraftState(draftFields[key], () => readDraft(draftFields[key]));
}

// A value that must be one of `allowed`, else `fallback` (a saved choice the catalog no
// longer offers, or none yet).
export function useDraftChoice(key, allowed, fallback = '') {
    return useDraftState(draftFields[key], () => {
        const saved = readDraft(draftFields[key]);
        return allowed.includes(saved) ? saved : fallback;
    });
}

// A list; `normalize` receives the saved list ([] when none) and returns the initial one.
export function useDraftArray(key, normalize = (saved) => saved) {
    return useDraftState(draftFields[key], () =>
        normalize(readDraftArray(draftFields[key])),
    );
}

export function useDraftObject(key) {
    return useDraftState(draftFields[key], () =>
        readDraftObject(draftFields[key]),
    );
}

const classFields = {
    equipment: equipmentFields,
    cantrips: cantripFields,
    spells: spellFields,
};

// Like `useDraftArray` for the lists kept per class ('equipment', 'cantrips', 'spells'),
// so switching class does not lose the picks made for another one. `useDraftState` only
// reads its initial value once per mount, so if the class changes while this stays mounted,
// re-sync the state to the new class's field instead of keeping the old class's array.
export function useClassDraftArray(
    group,
    characterClass,
    normalize = (saved) => saved,
) {
    const field = classFields[group][characterClass];
    const [value, update] = useDraftState(field, () =>
        normalize(readDraftArray(field)),
    );
    const fieldRef = useRef(field);
    useEffect(() => {
        if (fieldRef.current !== field) {
            fieldRef.current = field;
            update(normalize(readDraftArray(field)));
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps -- normalize is stable per call site
    }, [field]);
    return [value, update];
}
