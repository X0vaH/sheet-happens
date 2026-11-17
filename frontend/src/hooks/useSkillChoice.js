import { useCallback, useMemo } from 'react';
import {
    abilityNames,
    findByName,
    skillEntries,
} from '../utils/characterCreation/catalog.js';
import {
    availableClassSkills,
    backgroundSkills,
} from '../utils/characterCreation/characterSkills.js';
import { formatModifier } from '../utils/characterCreation/creationOptions.js';
import { toggleWithLimit } from '../utils/characterCreation/selection.js';
import { useCreationDraft } from './useCreationDraft.js';
import { useCreationOptions } from './useCreationOptions.js';
import { useDraftArray } from './useDraftState.js';

// The class skills chosen on top of the ones the background grants. `skills` lists every
// skill of the catalog with its ability, whether it is `granted` by the background, `active`
// (granted or chosen) and `locked` (not chosen and the class limit is reached).
export function useSkillChoice() {
    const options = useCreationOptions();
    const { characterClass, background, scores } = useCreationDraft();
    const abilities = abilityNames(options);
    const granted = backgroundSkills(options, background);
    const limit =
        findByName(options.classes, characterClass)?.skill_choice_count ?? 0;
    const [selected, setSelected] = useDraftArray('skills', (saved) =>
        availableClassSkills(options, background, saved).slice(0, limit),
    );

    const skills = useMemo(
        () =>
            skillEntries(options).map(([label, ability]) => {
                const isGranted = granted.includes(label);
                const active = isGranted || selected.includes(label);
                return {
                    label,
                    ability: abilities[ability],
                    modifier: formatModifier(scores[ability]),
                    granted: isGranted,
                    active,
                    locked: isGranted || (!active && selected.length === limit),
                };
            }),
        [options, abilities, granted, selected, scores, limit],
    );

    const toggle = useCallback(
        (label) => {
            if (granted.includes(label)) return;
            setSelected(toggleWithLimit(selected, label, limit));
        },
        [granted, selected, limit, setSelected],
    );

    return { limit, selected, skills, toggle };
}
