import {
    classLevelOne,
    findByName,
    grantedEquipmentOptions,
} from './catalog.js';
import { characterLanguages } from './characterLanguages.js';

// The catalog only exposes the spells a class actually grants/offers (level_one.spells for
// automatic grants, classes[].spells for the full choosable list) — chosen spell names always
// come from there, so every choice resolves to a real id; nothing is invented locally.
function spellsFromChoices(options, characterClass, chosen) {
    const known = findByName(options.classes, characterClass)?.spells ?? [];
    const ids = new Set(
        classLevelOne(options, characterClass).spells.map((spell) => spell.id),
    );
    const missing = [];
    for (const name of chosen) {
        const spell = findByName(known, name);
        if (spell) ids.add(spell.id);
        else missing.push(name);
    }
    return { spells: [...ids].map((id) => ({ spell_id: id })), missing };
}

function languageEntries(options, race, background, chosen) {
    const { racial } = characterLanguages(options, race, background, []);
    const entries = new Map();
    for (const [names, source] of [
        [racial, 'species'],
        [chosen, 'choice'],
    ]) {
        for (const name of names) {
            const language = findByName(options.languages, name);
            if (language && !entries.has(language.id))
                entries.set(language.id, source);
        }
    }
    return [...entries].map(([id, source]) => ({ language_id: id, source }));
}

/**
 * Builds the POST /api/characters body from the creation draft (catalog names/ids),
 * and lists the choices the catalog cannot store yet so the player is told.
 */
export function buildCharacterPayload(options, draft) {
    const species = findByName(options.species, draft.race);
    const background = findByName(options.backgrounds, draft.background);
    const characterClass = findByName(options.classes, draft.characterClass);
    const levelOne = classLevelOne(options, draft.characterClass);
    const { spells, missing: missingSpells } = spellsFromChoices(
        options,
        draft.characterClass,
        draft.spells,
    );

    return {
        payload: {
            order_index:
                (draft.orderIndex ?? '') === ''
                    ? null
                    : Number(draft.orderIndex),
            name: draft.name,
            alignment: draft.alignment || null,
            notes: draft.notes || null,
            species_id: species.id,
            background_id: background.id,
            class_id: characterClass.id,
            subclass_id:
                findByName(levelOne.subclasses, draft.subclass)?.id ?? null,
            abilities: options.abilities.map((ability, index) => ({
                ability_id: ability.id,
                score: draft.scores[index],
            })),
            skill_proficiencies: draft.skills.map((name) => ({
                skill_id: findByName(options.skills, name)?.id,
                proficiency_level: 'proficient',
            })),
            languages: languageEntries(
                options,
                draft.race,
                draft.background,
                draft.languages,
            ),
            tools: background.tools.map((tool) => ({
                tool_id: tool.id,
                proficiency_level: 'proficient',
            })),
            // Every level 1 species and class feature is sent so the character owns it; the option is set only when one was chosen.
            feature_choices: [
                ...species.features.map((feature) => ({
                    feature_id: feature.id,
                    feature_option_id: null,
                })),
                ...levelOne.features.map((feature) => ({
                    feature_id: feature.id,
                    feature_option_id: draft.featureOptions[feature.id] ?? null,
                })),
            ],
            spells,
            starting_equipment_choices: grantedEquipmentOptions(
                options,
                draft.characterClass,
                draft.equipment,
            ).map((option) => ({ starting_equipment_option_id: option.id })),
        },
        unsaved: { spells: missingSpells },
    };
}

// Laravel 422 responses: { message, errors: { field: [messages] } }.
export function validationMessages(error) {
    const data = error?.response?.data;
    if (data?.errors) return Object.values(data.errors).flat();
    return [data?.message || 'Une erreur est survenue, veuillez réessayer.'];
}
