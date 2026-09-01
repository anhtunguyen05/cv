# GitHub Issues — Phase 1

Suggested labels: `phase-1`, `cv`, `backend`, `frontend`, `database`, `testing`.

## #P1-1 — Add CV profile and immutable version schema

**Depends on:** Phase 0  
**Labels:** `phase-1`, `database`, `backend`

- [ ] Create `cv_profiles` and `cv_versions` migrations with foreign keys/indexes.
- [ ] Add models, relations, casts, factories, and safe JSON defaults.
- [ ] Add `schema_version`, `source`, and immutable-version rules.
- [ ] Add migration and relationship tests.

**Acceptance:** migrations run on the supported local database and an earlier
version remains unchanged after a later version is created.

## #P1-2 — Implement CV profile/version API

**Depends on:** `#P1-1`  
**Labels:** `phase-1`, `backend`, `security`

- [ ] Add Form Requests for profile and version input.
- [ ] Add Policies and ownership-aware route model binding.
- [ ] Add controllers, Resources, and v1 routes.
- [ ] Add create/read/update/delete and cross-user tests.

**Acceptance:** API responses match the documented contract and reject invalid
nested fields with `422`.

## #P1-3 — Build CV editor and version views

**Depends on:** `#P1-2`  
**Labels:** `phase-1`, `frontend`

- [ ] Add TypeScript CV types and API store.
- [ ] Build profile list, editor, save, and server-error states.
- [ ] Build immutable version list/detail views.
- [ ] Add focused UI tests or documented manual checks.

**Acceptance:** browser flow can create a CV, refresh, and open the previous
version without data loss.

## #P1-4 — Phase 1 checkpoint

**Depends on:** `#P1-1`, `#P1-2`, `#P1-3`  
**Labels:** `phase-1`, `testing`

- [ ] Run migration-fresh, API tests, and web checks.
- [ ] Perform a manual ownership and immutability smoke test.
- [ ] Update phase status and record unresolved decisions.

**Acceptance:** all exit criteria in the phase README pass.
