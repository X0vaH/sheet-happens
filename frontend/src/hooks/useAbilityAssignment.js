import {
    abilityMethods as methods,
    defaults,
    standardAbilityScores,
} from '../configs/characterCreation.js';
import {
    availableScores,
    rollAbilityPool,
} from '../utils/characterCreation/abilityAssignment.js';
import { abilityNames } from '../utils/characterCreation/catalog.js';
import { validScore } from '../utils/characterCreation/creationGuard.js';
import { useDraftArray, useDraftChoice } from './useDraftState.js';
import { useCreationOptions } from './useCreationOptions.js';

function normalizeScores(saved, count) {
    if (saved.length === count) {
        return saved.map((score) => (validScore(score) ? score : ''));
    }
    return [...defaults.abilities];
}

// Everything the ability scores step edits: the generation method, the pool of values it
// produced and the score given to each ability (typed by hand for the manual method).
export function useAbilityAssignment() {
    const labels = abilityNames(useCreationOptions());
    const [scores, setScores] = useDraftArray('abilities', (saved) =>
        normalizeScores(saved, labels.length),
    );
    const [pool, setPool] = useDraftArray('abilityPool');
    const [method, setMethod] = useDraftChoice('abilityMethod', methods);

    function generatePool(next) {
        setPool(next);
        setScores([...defaults.abilities]);
    }

    function selectMethod(value) {
        setMethod(value);
        if (value === 'Manuel') setPool([]);
        else if (value === 'Standard') generatePool([...standardAbilityScores]);
        else generatePool(rollAbilityPool(labels.length));
    }

    function setScore(index, value) {
        setScores(
            scores.map((score, slot) => (slot === index ? value : score)),
        );
    }

    // Moves ability `index` to the next (or previous) value still free in the pool.
    function cycleScore(index, direction) {
        const choices = ['', ...new Set(availableScores(pool, scores, index))];
        const position = choices.indexOf(scores[index]);
        setScore(
            index,
            choices[(position + direction + choices.length) % choices.length],
        );
    }

    return {
        labels,
        methods,
        method,
        manual: method === 'Manuel',
        pool,
        scores,
        available: availableScores(pool, scores),
        selectMethod,
        setScore,
        cycleScore,
    };
}
