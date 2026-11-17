import { useCallback, useMemo } from 'react';
import { languageDescription } from '../utils/characterCreation/catalogExtras.js';
import { names } from '../utils/characterCreation/catalog.js';
import {
    characterLanguages,
    languageChoiceCount,
    normalizeLanguages,
} from '../utils/characterCreation/characterLanguages.js';
import { useCreationDraft } from './useCreationDraft.js';
import { useCreationOptions } from './useCreationOptions.js';
import { useDraftArray } from './useDraftState.js';

// The free language choices a background grants: one slot per choice, on top of the
// languages the race already speaks (`known`). Two slots never hold the same language.
export function useLanguageSlots(background) {
    const options = useCreationOptions();
    const { race } = useCreationDraft();
    const [chosen, setChosen] = useDraftArray('languages', (saved) =>
        normalizeLanguages(options, race, background, saved),
    );
    const limit = languageChoiceCount(options, race, background);
    const known = useMemo(
        () => characterLanguages(options, race, background, []).spoken,
        [options, race, background],
    );
    const catalog = useMemo(() => names(options.languages), [options]);

    // Languages slot `index` may still pick: not known, not taken by another slot.
    const optionsFor = useCallback(
        (index) =>
            catalog.filter(
                (value) =>
                    !known.includes(value) &&
                    (!chosen.includes(value) || chosen[index] === value),
            ),
        [catalog, known, chosen],
    );
    const choose = useCallback(
        (index, value) =>
            setChosen(
                Array.from({ length: limit }, (_, slot) =>
                    slot === index ? value : (chosen[slot] ?? ''),
                ),
            ),
        [limit, chosen, setChosen],
    );
    const describe = useCallback(
        (index) => languageDescription(options, chosen[index]),
        [options, chosen],
    );
    // Drops the picks the new background makes invalid or superfluous.
    const resetFor = useCallback(
        (nextBackground) =>
            setChosen(
                normalizeLanguages(options, race, nextBackground, chosen),
            ),
        [options, race, chosen, setChosen],
    );

    return { limit, known, chosen, optionsFor, choose, describe, resetFor };
}
