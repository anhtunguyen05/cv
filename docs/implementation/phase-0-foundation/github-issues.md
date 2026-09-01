# GitHub Issues — Phase 0

Create these issues in order. Suggested labels: `phase-0`, `foundation`,
`backend`, `frontend`, `testing`.

## #P0-1 — Confirm scaffold commands and API baseline

**Depends on:** none  
**Labels:** `phase-0`, `foundation`, `testing`

### Tasks

- [ ] Record verified web, API, and worker commands.
- [ ] Verify `GET /api/health`, `GET /up`, and worker health aliases.
- [ ] Record local SQLite and Docker MySQL differences.
- [ ] Add or update setup notes without claiming domain features exist.

### Acceptance criteria

- A clean checkout can run the baseline checks.
- Existing health behavior is covered by tests or existing test evidence.

## #P0-2 — Establish Laravel API v1 and auth boundary

**Depends on:** `#P0-1`  
**Labels:** `phase-0`, `backend`, `security`

### Tasks

- [ ] Choose and document the browser auth mechanism.
- [ ] Add `/api/v1` route grouping and protected-route middleware.
- [ ] Add `GET /api/v1/auth/me` or the selected equivalent.
- [ ] Return the documented validation/auth error shape.
- [ ] Add unauthenticated and cross-user authorization tests.

### Acceptance criteria

- No protected endpoint trusts a client-supplied user ID.
- Unauthenticated requests receive a stable JSON response.

## #P0-3 — Add web API client and application shell

**Depends on:** `#P0-2`  
**Labels:** `phase-0`, `frontend`

### Tasks

- [ ] Add `VITE_API_BASE_URL` example configuration.
- [ ] Implement one typed API client and normalized API errors.
- [ ] Add auth store, route guard, loading, and error states.
- [ ] Replace starter navigation with an application shell.

### Acceptance criteria

- Components do not duplicate base URL or auth handling.
- The app can show an API failure without exposing a stack trace.

## #P0-4 — Add phase verification gate

**Depends on:** `#P0-1`, `#P0-2`, `#P0-3`  
**Labels:** `phase-0`, `testing`

### Tasks

- [ ] Add focused API Feature tests.
- [ ] Run frontend type-check, build, and lint.
- [ ] Run worker unit tests.
- [ ] Document the phase checkpoint result.

### Acceptance criteria

- All three application checks pass from their application directories.
- Any environment limitation is documented as a blocker, not silently skipped.
