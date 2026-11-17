import { readCreationDraft } from '../utils/characterCreation/creationSnapshot.js';

// The draft lives in a plain in-memory map, so this reads it on every render
// rather than subscribing to it: pages that edit a field keep it in local state.
export function useCreationDraft() {
    return readCreationDraft();
}
