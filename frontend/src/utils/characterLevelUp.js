/**
 * Le niveau maximum est décidé par l'API : `nextLevel` vaut null quand il est atteint.
 * Un `nextLevel` absent (undefined) est volontairement traité comme « pas de montée
 * possible » : sans l'information de l'API, on n'autorise pas la montée de niveau.
 */
export function canLevelUp(character) {
    return character.nextLevel !== null && character.nextLevel !== undefined;
}

export function levelUpConfig(options) {
    return {
        hitDie: options.hit_die,
        notes: options.progression.notes,
        features: options.available_options.features ?? [],
        feats: options.available_options.feats ?? [],
        grantedSpells: options.granted_spells ?? [],
        subclasses: options.available_options.subclasses ?? [],
        abilityPoints: options.progression.ability_points,
        spellCount: options.progression.spell_count,
        cantripCount: options.progression.cantrip_count,
        slots: options.progression.slots,
        spells: options.available_options.spells ?? [],
    };
}

export function buildLevelUpPayload(
    options,
    { feat, featureChoices, increases, spells, cantrips },
) {
    return {
        target_level: options.next_level,
        hit_point_method: 'fixed',
        feat_id: feat ?? null,
        feature_choices: Object.entries(featureChoices).map(
            ([featureId, optionIds]) => ({
                feature_id: Number(featureId),
                option_ids: optionIds,
            }),
        ),
        ability_increases: options.available_options.abilities
            .filter((ability) => increases[ability.id])
            .map((ability) => ({
                ability_id: ability.id,
                increase_amount: increases[ability.id],
            })),
        spell_ids: [...spells, ...cantrips],
    };
}
