export function availableScores(pool, assigned, excludedIndex = -1) {
    const remaining = [...pool];
    assigned.forEach((score, index) => {
        if (index === excludedIndex || score === '') return;
        const position = remaining.indexOf(score);
        if (position !== -1) remaining.splice(position, 1);
    });
    return remaining;
}

export function isCompleteAssignment(pool, assigned) {
    const remaining = [...pool];
    return (
        pool.length === assigned.length &&
        assigned.every((score) => {
            const position = remaining.indexOf(score);
            if (position === -1) return false;
            remaining.splice(position, 1);
            return true;
        })
    );
}

// `count` scores, each rolled as 4d6 keeping the three highest dice.
export function rollAbilityPool(count) {
    return Array.from({ length: count }, () => {
        const dice = Array.from(
            { length: 4 },
            () => 1 + Math.floor(Math.random() * 6),
        ).sort((a, b) => a - b);
        return dice.slice(1).reduce((sum, die) => sum + die, 0);
    });
}
