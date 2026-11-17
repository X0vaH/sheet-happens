import { draftFields } from '../../configs/characterStorage.js';
import { findByName, names } from './catalog.js';
import { readDraft, readDraftArray } from './creationDraft.js';

// Species grant fixed languages (Species.languages); backgrounds only grant a number of
// free choices (Background.language_choice_count), no fixed languages of their own.
export function characterLanguages(options, race, background, chosenLanguage) {
    const languages = names(options.languages);
    const racial = names(findByName(options.species, race)?.languages);
    const chosen = Array.isArray(chosenLanguage)
        ? chosenLanguage
        : [chosenLanguage];
    const spoken = [
        ...new Set(
            [...racial, ...chosen].filter((value) => languages.includes(value)),
        ),
    ];
    return { racial, spoken };
}

export function languageChoiceCount(options, race, background) {
    return (
        findByName(options.backgrounds, background)?.language_choice_count ?? 0
    );
}

export function readChosenLanguages() {
    return readDraftArray(
        draftFields.languages,
        readDraft(draftFields.language)
            ? [readDraft(draftFields.language)]
            : [],
    );
}

export function normalizeLanguages(options, race, background, chosen) {
    const languages = names(options.languages);
    const known = characterLanguages(options, race, background, []).spoken;
    return [
        ...new Set(
            chosen.filter(
                (value) => languages.includes(value) && !known.includes(value),
            ),
        ),
    ].slice(0, languageChoiceCount(options, race, background));
}

export function hasCompleteLanguages(
    options,
    race,
    background,
    chosen = readChosenLanguages(),
) {
    return (
        chosen.length === languageChoiceCount(options, race, background) &&
        normalizeLanguages(options, race, background, chosen).length ===
            chosen.length
    );
}
