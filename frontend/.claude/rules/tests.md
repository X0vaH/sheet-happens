---
description: Playwright tests — fixtures, mocked API, no Laravel required
paths:
    - 'tests/**'
---

# Tests (Playwright)

## Imports

- **Protected pages / creation flows**: import `test` and `expect` from `tests/helpers/test.js`, not from `@playwright/test` directly. That fixture mocks session, creation catalog, and `POST /api/characters`.
- **Auth-focused specs**: import from `tests/helpers/auth.js` when exercising `AuthProvider` with a mocked `/api/user`. Use the fixture option `authenticated: false` for unauthenticated login flows (see CONTRIBUTING.md).

## Mock API

- `API_URL` in `tests/helpers/test.js` must match `VITE_API_URL` in `playwright.config.js` (`http://api.test`). Do not point tests at a real Laravel backend.
- Override behavior via fixture hooks (e.g. `characterResponse`, `createdCharacters` on the extended test) rather than duplicating route handlers.

## Running

- `npm run test` starts Vite on `127.0.0.1:4173` via `webServer` in `playwright.config.js`.
- Browser channel is **Microsoft Edge** (`channel: 'msedge'`). Tests assume Edge is installed.

## Style

- One spec file per functional area (`creation.spec.js`, `spells.spec.js`, etc.).
- Prefer extending existing helpers over new global mocks. Keep new specs aligned with `BYPASS_AUTH = false` in `AuthProvider` — tests go through real protected routes with intercepted API calls.
