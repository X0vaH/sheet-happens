import { test as base, expect } from '@playwright/test';
import { creationOptions } from '../fixtures/creationOptions.js';

// Must match VITE_API_URL given to the dev server in playwright.config.js.
export const API_URL = 'http://api.test';

const corsHeaders = {
    'Access-Control-Allow-Origin': 'http://127.0.0.1:4173',
    'Access-Control-Allow-Credentials': 'true',
    'Access-Control-Allow-Methods': 'GET, POST, DELETE, OPTIONS',
    'Access-Control-Allow-Headers':
        'Accept, Content-Type, X-Requested-With, X-XSRF-TOKEN',
};

// Stands in for the Laravel API: an authenticated session, the creation catalog,
// GET/POST /api/characters and GET/DELETE /api/characters/{id}. Tests can override
// `characters`, `charactersResponse`, `characterDetails`, `characterResponse` and
// `deleteResponse`, and read `createdCharacters` and `deletedCharacterIds`.
export const test = base.extend({
    api: [
        async ({ page }, use) => {
            const api = {
                characters: [],
                // { status, body } to replace the GET /api/characters reply (ex. an error).
                charactersResponse: null,
                // Character returned by GET /api/characters/{id}, keyed by id; others are 404.
                characterDetails: {},
                createdCharacters: [],
                // Ids received by DELETE /api/characters/{id}.
                deletedCharacterIds: [],
                // { status, body } to replace the DELETE /api/characters/{id} reply.
                deleteResponse: { status: 204 },
                characterResponse: {
                    status: 201,
                    body: {
                        message: 'Personnage créé avec succès',
                        character: { id: 1 },
                    },
                },
            };
            await page.route(API_URL + '/**', async (route) => {
                const request = route.request();
                const path = new URL(request.url()).pathname;
                const reply = (status, body) =>
                    route.fulfill({
                        status,
                        headers: corsHeaders,
                        contentType: 'application/json',
                        body: body === undefined ? '' : JSON.stringify(body),
                    });
                if (request.method() === 'OPTIONS') return reply(204);
                if (path === '/sanctum/csrf-cookie') return reply(204);
                if (path === '/api/user')
                    return reply(200, {
                        id: 1,
                        name: 'Testeur',
                        email: 'test@example.com',
                    });
                if (path === '/api/rulesets/current/character-creation-options')
                    return reply(200, { options: creationOptions });
                if (path === '/api/characters' && request.method() === 'GET') {
                    if (api.charactersResponse)
                        return reply(
                            api.charactersResponse.status,
                            api.charactersResponse.body,
                        );
                    return reply(200, { characters: api.characters });
                }
                const detailMatch = path.match(/^\/api\/characters\/(\d+)$/);
                if (detailMatch && request.method() === 'GET') {
                    const character = api.characterDetails[detailMatch[1]];
                    return character
                        ? reply(200, { character })
                        : reply(404, { message: 'Ressource non trouvée.' });
                }
                if (detailMatch && request.method() === 'DELETE') {
                    api.deletedCharacterIds.push(Number(detailMatch[1]));
                    return reply(
                        api.deleteResponse.status,
                        api.deleteResponse.body,
                    );
                }
                if (path === '/api/characters' && request.method() === 'POST') {
                    api.createdCharacters.push(request.postDataJSON());
                    return reply(
                        api.characterResponse.status,
                        api.characterResponse.body,
                    );
                }
                return reply(404, { message: 'Not found' });
            });
            await use(api);
        },
        { auto: true },
    ],
});

export { expect };
