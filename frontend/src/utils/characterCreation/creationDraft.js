import { storageKeys } from '../../configs/characterStorage.js';

const memory = new Map();

// Bumped on every write, so a snapshot of the draft (see creationSnapshot.js) can tell
// whether it is still up to date without re-reading everything.
let version = 0;

export function draftVersion() {
    return version;
}

export function readDraft(field) {
    const key = storageKeys[field];
    if (!key) return '';
    return memory.get(field) ?? '';
}

export function saveDraft(field, value) {
    const key = storageKeys[field];
    if (!key) return false;
    memory.set(field, value);
    version++;
    return true;
}

export function clearDraft() {
    memory.clear();
    version++;
}

export function readDraftArray(field, fallback = []) {
    try {
        const value = JSON.parse(readDraft(field));
        return Array.isArray(value) ? value : fallback;
    } catch {
        return fallback;
    }
}

export function readDraftObject(field, fallback = {}) {
    try {
        const value = JSON.parse(readDraft(field));
        return value && typeof value === 'object' && !Array.isArray(value)
            ? value
            : fallback;
    } catch {
        return fallback;
    }
}
