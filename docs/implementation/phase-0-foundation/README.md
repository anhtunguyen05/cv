# Phase 0 — Foundation

Status: `planned`

## Outcome

Prepare the Vue, Laravel, and worker scaffolds for product implementation with
a stable API boundary, authentication boundary, shared client conventions, and
repeatable verification.

## Scope

In scope: API v1 routing, browser auth decision, identity endpoint, frontend
API client, application shell, environment examples, and baseline tests.

Out of scope: CV tables, AI providers, queues, matching, and export.

## Design

Keep Laravel as the trusted state boundary. Use a versioned route group at
`/api/v1`. The frontend must use one API client configured by
`VITE_API_BASE_URL`; components must not construct URLs independently. Choose
one browser auth mechanism and document it before implementing protected routes.

Expected locations:

```text
apps/api/routes/api.php
apps/api/app/Http/Controllers/Api/V1/
apps/api/app/Http/Requests/
apps/api/tests/Feature/Api/V1/
apps/web/src/lib/api-client.ts
apps/web/src/stores/auth.ts
apps/web/src/layouts/
```

## Work sequence

1. Confirm local/Docker commands and preserve `/api/health` and `/up`.
2. Add the `/api/v1` route group and auth boundary.
3. Add the identity endpoint and consistent JSON errors.
4. Add the frontend API client, auth store, and authenticated shell.
5. Add negative API tests and frontend checks.

## Verification

```powershell
cd apps/api; php artisan test
cd ../web; npm run type-check; npm run build; npm run lint
cd ../worker; python -m unittest discover -s tests
```

## Checkpoint / exit criteria

- Health endpoints remain green.
- Protected v1 routes reject unauthenticated requests.
- Ownership/auth behavior has a Feature test.
- The web app displays loading, validation, and API failure states.
- A clean developer can follow the documented setup without an AI key.

See [`github-issues.md`](github-issues.md) for the executable issue list.
