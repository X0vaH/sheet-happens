import api, { getCsrfCookie } from './axios.js';

export async function fetchLevelUpOptions(characterId, signal) {
    const { data } = await api.get(
        `/api/characters/${characterId}/level-up/options`,
        { signal },
    );
    return data;
}

export async function applyLevelUp(characterId, payload) {
    await getCsrfCookie();
    await api.post(`/api/characters/${characterId}/level-up`, payload);
}
