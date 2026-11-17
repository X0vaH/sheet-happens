import { findByName, names } from './catalog.js';

export function backgroundSkills(options, background) {
    return names(findByName(options.backgrounds, background)?.skills);
}

export function availableClassSkills(options, background, selected) {
    const granted = backgroundSkills(options, background);
    const skills = names(options.skills);
    return [...new Set(selected)].filter(
        (name) => skills.includes(name) && !granted.includes(name),
    );
}

export function hasCompleteSkills(
    options,
    characterClass,
    background,
    selected,
) {
    const limit = findByName(
        options.classes,
        characterClass,
    )?.skill_choice_count;
    return (
        selected.length === limit &&
        availableClassSkills(options, background, selected).length === limit
    );
}

export function characterSkills(options, background, selected) {
    return [
        ...new Set([
            ...backgroundSkills(options, background),
            ...availableClassSkills(options, background, selected),
        ]),
    ];
}
