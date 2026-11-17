// Fonctions partagées par l'accueil et la fiche pour lire un personnage de l'API.

export function formatClasse(character) {
    const [firstClass] = character.classes ?? [];
    if (!firstClass) return 'Classe inconnue';
    const className = firstClass.class_definition?.name ?? 'Classe inconnue';
    return firstClass.subclass
        ? `${className} (${firstClass.subclass.name})`
        : className;
}

export function getLevel(character) {
    const levels = (character.levels ?? []).map(
        (entry) => entry.character_level,
    );
    return levels.length > 0 ? Math.max(...levels) : 1;
}

// `classe` est le libellé produit par formatClasse, avec ou sans sous-classe.
export function isFighter(classe) {
    return classe === 'Guerrier' || classe?.startsWith('Guerrier (');
}

// Répartit les personnages dans `maxCharacters` emplacements (null = vide).
// Un personnage va à l'emplacement de son orderIndex s'il est valide et libre ;
// sinon (doublon, hors limites, absent) il prend le premier emplacement libre.
export function buildSlots(characters, maxCharacters) {
    const slots = Array(maxCharacters).fill(null);
    const unplaced = [];

    for (const character of characters) {
        const index = character.orderIndex;
        if (
            Number.isInteger(index) &&
            index >= 0 &&
            index < maxCharacters &&
            slots[index] === null
        ) {
            slots[index] = character;
        } else {
            unplaced.push(character);
        }
    }

    for (const character of unplaced) {
        const freeIndex = slots.indexOf(null);
        if (freeIndex === -1) break;
        slots[freeIndex] = character;
    }

    return slots;
}
