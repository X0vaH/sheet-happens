import {
    abilityMethods,
    scoreBounds,
} from '../../configs/characterCreation.js';
import { isCompleteAssignment } from './abilityAssignment.js';
import {
    abilityNames,
    choiceFeatures,
    classLevelOne,
    equipmentChoiceGroups,
    findByName,
} from './catalog.js';
import { isAlignment } from './catalogExtras.js';
import {
    hasCompleteLanguages,
    languageChoiceCount,
} from './characterLanguages.js';
import { availableClassSkills } from './characterSkills.js';
import { validScore } from './creationGuard.js';
import { hasSpellStep } from './creationFlow.js';
import { readCreationDraft } from './creationSnapshot.js';
import { readCharacterSpells, spellChoiceLimits } from './spellOptions.js';

// Each step's missing-choice check works on a creation draft (see readCreationDraft) and
// returns the labels shown in the "Il manque des éléments" dialog; an empty list means the
// step is complete. The guard, the step pages and the layout all read this one table.

function classMissing(options, draft) {
    const { subclasses } = classLevelOne(options, draft.characterClass);
    return [
        !findByName(options.classes, draft.characterClass) &&
            'Classe du personnage',
        subclasses.length > 0 &&
            !findByName(subclasses, draft.subclass) &&
            'Sous-classe',
        ...choiceFeatures(options, draft.characterClass)
            .filter(
                (feature) =>
                    !feature.options.some(
                        (option) =>
                            option.id === draft.featureOptions[feature.id],
                    ),
            )
            .map((feature) => feature.name),
    ];
}

function equipmentMissing(options, draft) {
    return equipmentChoiceGroups(options, draft.characterClass)
        .filter(
            (group, index) =>
                !group.options.some(
                    (option) => option.id === draft.equipment[index],
                ),
        )
        .map((group) => group.label);
}

function abilitiesMissing(options, draft) {
    const { scores, abilityMethod, abilityPool } = draft;
    if (!abilityMethods.includes(abilityMethod)) {
        return ['Choisissez une méthode pour générer les caractéristiques.'];
    }
    if (abilityMethod === 'Manuel') {
        return abilityNames(options)
            .filter((_, index) => !validScore(scores[index]))
            .map(
                (label) =>
                    `${label} : saisissez un nombre entier entre ${scoreBounds.min} et ${scoreBounds.max}.`,
            );
    }
    const assigned =
        abilityPool.length > 0 &&
        scores.every(validScore) &&
        isCompleteAssignment(abilityPool, scores);
    if (assigned) return [];
    return [
        abilityPool.length === 0
            ? 'Choisissez une méthode pour générer les caractéristiques.'
            : 'Attribuez toutes les valeurs disponibles aux six caractéristiques.',
    ];
}

function backgroundMissing(options, draft) {
    const { race, background, languages } = draft;
    const slots = Array.from(
        { length: languageChoiceCount(options, race, background) },
        (_, index) => !languages[index] && 'Langue ' + (index + 1),
    ).filter(Boolean);
    const invalidLanguages =
        slots.length === 0 &&
        !hasCompleteLanguages(options, race, background, languages) &&
        'Langues';
    return [
        !findByName(options.backgrounds, background) && 'Historique',
        ...slots,
        invalidLanguages,
    ];
}

function skillsMissing(options, draft) {
    const limit =
        findByName(options.classes, draft.characterClass)?.skill_choice_count ??
        0;
    const valid = availableClassSkills(options, draft.background, draft.skills);
    if (draft.skills.length === limit && valid.length === limit) return [];
    if (valid.length > limit) {
        return [
            'Retirez ' + (valid.length - limit) + ' compétence(s) en trop.',
        ];
    }
    return ['Choisissez encore ' + (limit - valid.length) + ' compétence(s).'];
}

function spellsMissing(options, draft) {
    const { cantripLimit, spellLimit } = spellChoiceLimits(
        options,
        draft.characterClass,
    );
    const chosen = readCharacterSpells(options, draft.characterClass);
    return [
        chosen.cantrips.length !== cantripLimit &&
            'Choisissez encore ' +
                (cantripLimit - chosen.cantrips.length) +
                ' sort(s) mineur(s).',
        chosen.levelOne.length !== spellLimit &&
            'Choisissez encore ' +
                (spellLimit - chosen.levelOne.length) +
                ' sort(s) de niveau 1.',
    ];
}

// In wizard order. `path` is relative to /character/create; `applies` drops the step from
// the flow for characters that do not need it (spells for non-casters).
export const creationSteps = [
    {
        id: 'info',
        label: 'Informations',
        path: '',
        missing: (options, draft) => [
            !draft.name.trim() && 'Nom du personnage',
            !isAlignment(options, draft.alignment) && 'Alignement',
        ],
    },
    {
        id: 'race',
        label: 'Race',
        path: '/race',
        missing: (options, draft) => [
            !findByName(options.species, draft.race) && 'Race du personnage',
        ],
    },
    { id: 'class', label: 'Classe', path: '/class', missing: classMissing },
    {
        id: 'equipment',
        label: 'Équipement',
        path: '/equipment',
        missing: equipmentMissing,
    },
    {
        id: 'abilities',
        label: 'Caractéristiques',
        path: '/abilities',
        missing: abilitiesMissing,
    },
    {
        id: 'background',
        label: 'Historique',
        path: '/background',
        missing: backgroundMissing,
    },
    {
        id: 'skills',
        label: 'Compétences',
        path: '/skills',
        missing: skillsMissing,
    },
    {
        id: 'spells',
        label: 'Sorts',
        path: '/spells',
        applies: hasSpellStep,
        missing: spellsMissing,
    },
    { id: 'summary', label: 'Résumé', path: '/summary', missing: () => [] },
];

const applies = (step, options, characterClass) =>
    step.applies?.(options, characterClass) ?? true;

export function activeSteps(options, characterClass) {
    return creationSteps.filter((step) =>
        applies(step, options, characterClass),
    );
}

// Whether the wizard is at or past step `id` (true outside a step, e.g. in tests).
export function hasReachedStep(current, id) {
    const index = (target) => creationSteps.findIndex((s) => s.id === target);
    return index(current) === -1 || index(current) >= index(id);
}

export const stepRoute = (step) => '/character/create' + step.path;

function neighbour(options, id, characterClass, offset) {
    const steps = activeSteps(options, characterClass);
    const index = steps.findIndex((step) => step.id === id);
    const target = index === -1 ? undefined : steps[index + offset];
    return target ? stepRoute(target) : null;
}

export const previousStepPath = (options, id, characterClass) =>
    neighbour(options, id, characterClass, -1);

export const nextStepPath = (options, id, characterClass) =>
    neighbour(options, id, characterClass, 1);

export function missingFor(id, options, draft = readCreationDraft()) {
    const step = creationSteps.find((entry) => entry.id === id);
    return step.missing(options, draft).filter(Boolean);
}

// Route of the first step before `id` whose choices are missing or invalid, or null when
// the user may stay on `id`. Render `<Navigate to={redirect} replace />` when it is set.
export function incompleteStepPath(options, id, draft = readCreationDraft()) {
    const steps = activeSteps(options, draft.characterClass);
    for (const step of steps) {
        if (step.id === id) break;
        if (missingFor(step.id, options, draft).length > 0)
            return stepRoute(step);
    }
    // A step the character skips (spells for a non-caster) sends the user to the step before.
    if (!steps.some((step) => step.id === id)) {
        const before = creationSteps.findIndex((step) => step.id === id);
        return stepRoute(
            creationSteps
                .slice(0, before)
                .reverse()
                .find((step) => steps.includes(step)),
        );
    }
    return null;
}
