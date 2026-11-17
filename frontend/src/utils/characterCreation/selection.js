// Adds `value` to `selected`, removes it if already there, and ignores additions
// once `limit` values are selected.
export function toggleWithLimit(selected, value, limit) {
    if (selected.includes(value)) {
        return selected.filter((entry) => entry !== value);
    }
    return selected.length < limit ? [...selected, value] : selected;
}
