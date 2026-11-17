import { equipmentFields } from '../../configs/characterStorage.js';
import { equipmentChoiceGroups, findByName } from './catalog.js';

export function equipmentField(characterClass) {
    return equipmentFields[characterClass];
}

export function hasSpellStep(options, characterClass) {
    const profile = findByName(options.classes, characterClass);
    return Boolean(
        profile &&
        (profile.cantrip_choice_count > 0 || profile.spell_choice_count > 0),
    );
}

export function hasClassChoices(options, characterClass, equipment) {
    const groups = equipmentChoiceGroups(options, characterClass);
    return (
        Array.isArray(equipment) &&
        equipment.length === groups.length &&
        equipment.every((value, index) =>
            groups[index].options.some((option) => option.id === value),
        )
    );
}
