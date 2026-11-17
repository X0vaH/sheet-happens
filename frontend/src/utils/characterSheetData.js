/**
 * Données du personnage utilisées par la page de montée de niveau. Les statistiques
 * (bonus, compétences, classe d'armure…) sont calculées par l'API et lues par la fiche.
 */
export function characterSheetData(character) {
    return {
        id: String(character.id),
        name: character.name,
        level: Math.max(
            1,
            ...(character.levels ?? []).map((entry) => entry.character_level),
        ),
        hp: Number(character.max_health_points),
        nextLevel: character.next_level ?? null,
    };
}
