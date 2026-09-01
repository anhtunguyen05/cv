# GitHub Issues — Phase 3

Suggested labels: `phase-3`, `ai`, `patches`, `security`, `backend`, `frontend`.

## #P3-1 — Add interview and patch persistence

**Depends on:** Phase 2  
**Labels:** `phase-3`, `database`, `backend`

- [ ] Add interview session/message and patch migrations.
- [ ] Add models, relations, casts, factories, and indexes.
- [ ] Add patch status transition rules and timestamps.
- [ ] Add ownership tests.

**Acceptance:** interview and patch records cannot cross user boundaries.

## #P3-2 — Implement patch contract and validator

**Depends on:** `#P3-1`  
**Labels:** `phase-3`, `security`, `backend`

- [ ] Define patch DTO and allowed section/field operations.
- [ ] Validate evidence sources and content limits.
- [ ] Reject unknown fields, missing items, and stale `old_value`.
- [ ] Add unit tests for valid and unsafe proposals.

**Acceptance:** no invalid patch DTO can reach the apply service.

## #P3-3 — Add provider-neutral interview and patch services

**Depends on:** `#P3-2`  
**Labels:** `phase-3`, `ai`, `backend`

- [ ] Define question and patch generator interfaces.
- [ ] Add fake provider for tests/local development.
- [ ] Add provider adapter boundary and timeout/error mapping.
- [ ] Persist proposals only after validation.

**Acceptance:** tests run without external credentials and provider output cannot
call persistence directly.

## #P3-4 — Implement atomic patch approval API

**Depends on:** `#P3-2`, `#P3-3`  
**Labels:** `phase-3`, `backend`, `security`

- [ ] Add interview/message/generate-patch endpoints.
- [ ] Add patch list, accept, reject, and regenerate endpoints.
- [ ] Implement transactional new-version creation.
- [ ] Add stale patch and retry tests.

**Acceptance:** approval either creates a new version and marks applied, or
leaves all state unchanged.

## #P3-5 — Build interview and patch review UI

**Depends on:** `#P3-4`  
**Labels:** `phase-3`, `frontend`

- [ ] Build conversation states and answer submission.
- [ ] Build patch cards with evidence and old/new values.
- [ ] Add accept/reject/edit/regenerate and conflict states.
- [ ] Show resulting version after approval.

**Acceptance:** a user can complete one evidence-to-approved-version flow.

## #P3-6 — Phase 3 checkpoint

**Depends on:** `#P3-4`, `#P3-5`  
**Labels:** `phase-3`, `testing`

- [ ] Run API, provider-fake, and frontend checks.
- [ ] Manually verify reject, stale, and approve flows.
- [ ] Record human-approval safety result.

**Acceptance:** all phase exit criteria pass.
