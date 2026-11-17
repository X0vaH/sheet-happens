import api, { getCsrfCookie } from './axios.js';

export async function fetchCreationOptions() {
    const { data } = await api.get(
        '/api/rulesets/current/character-creation-options',
    );
    return data.options;
}

export async function createCharacter(payload) {
    await getCsrfCookie();
    const { data } = await api.post('/api/characters', payload);
    return data.character;
}
