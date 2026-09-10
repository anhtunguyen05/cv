# Epic 1 Decisions and Coordination

Recommendations are review proposals, not approved requirements. Before a
dependent story is published, every applicable decision needs one owner, an
approved resolution, and evidence in the form `approver, YYYY-MM-DD`.

## Decision register

| ID | Status | Owner | Decision required | Recommended starting point | Resolution | Evidence | Blocks |
| --- | --- | --- | --- | --- | --- | --- | --- |
| E1-DEC-001 | `open` | `unassigned` | Sanctum origin/session topology | Same-origin production; Vite proxy or explicit credentialed dev origin; freeze CORS, stateful domains, cookie attributes, CSRF bootstrap/recovery, regeneration and invalidation | `pending` | `pending` | 1.1, 1.2, all protected stories |
| E1-DEC-002 | `open` | `unassigned` | Account identity policy | Freeze public User ID serialization, name/email normalization, password policy, email verification deferral, duplicate privacy acceptance, register/login limiters, and authenticated registration behavior | `pending` | `pending` | 1.1, 1.2 |
| E1-DEC-003 | `open` | `unassigned` | CV Profile public schema | Freeze every personal/section field, required/optional state, Profile count/title uniqueness policy, nested field path, list/detail/write endpoints, response shape, and relational/JSON boundary | `pending` | `pending` | 1.3–1.8 |
| E1-DEC-004 | `open` | `unassigned` | CV field semantics and limits | Freeze Unicode/trim policy, character and byte limits, URL/date semantics, collection sizes, ordering, duplicate-item rules, and safe rendering constraints | `pending` | `pending` | 1.3–1.8 |
| E1-DEC-005 | `open` | `unassigned` | Profile write and concurrency model | Prefer aggregate-safe section writes with an explicit `updated_at` or revision precondition; freeze omission/removal semantics, stale conflict, autosave/manual save, and ambiguous-success reconciliation | `pending` | `pending` | 1.3–1.7 |
| E1-DEC-006 | `open` | `unassigned` | CV Version snapshot contract | Freeze Version name policy, snapshot schema/version and backward-compatible reader policy, source fields, transaction boundary, ordering/pagination, whether duplicate names are allowed, and the rule that old snapshots are never rewritten during schema evolution | `pending` | `pending` | 1.8 and downstream Epics 2–4 |
| E1-DEC-007 | `open` | `unassigned` | Web navigation and protected-state behavior | Freeze first authenticated route, guest/auth redirects, safe return destination, expiry UX, cache clearing, unsaved-change handling, and retry classifications | `pending` | `pending` | all web tasks |
| E1-DEC-008 | `open` | `unassigned` | Frontend verification enablement | Assign a separate shared enablement item for Vitest/Playwright, disposable database orchestration, commands, CI ownership, and contract fixtures | `pending` | `pending` | component/E2E tasks |
| E1-DEC-009 | `open` | `unassigned` | Account endpoint and failure contract | Freeze auth routes, request fields, success statuses, error codes/messages/details, logout idempotency, current-account cache headers, malformed transport behavior, and ambiguous-success reconciliation | `pending` | `pending` | 1.1, 1.2 |

## Cross-story coordination records

### E1-COORD-AUTH-001 — User, session, and account contract

- Stories: `1-1-register-an-account`, `1-2-sign-in-and-sign-out` and every
  later protected story.
- Decision owner: `unassigned`.
- Resolution: `pending E1-DEC-001, E1-DEC-002, E1-DEC-007, E1-DEC-009`.
- Reserved boundary: auth middleware/configuration, shared web API client,
  session state, Public User fixture, and current-account endpoint.
- Sequence/merge rule: one owner lands the approved shared boundary; story
  tasks consume it and do not redefine it.

### E1-COORD-PROFILE-001 — CV Profile aggregate and editor contract

- Stories: `1-3-create-a-cv-profile` through
  `1-7-manage-supplementary-cv-sections`, plus Story 1.8 as read-only consumer.
- Decision owner: `unassigned`.
- Resolution: `pending E1-DEC-003, E1-DEC-004, E1-DEC-005`.
- Reserved boundary: migrations/models, aggregate service, policy, Profile API
  resource and Form Requests, frontend feature API/schema/editor state, and
  canonical contract fixtures.
- Sequence/merge rule: freeze fixtures first; assign non-overlapping section
  modules to parallel stories; one integration owner serializes changes to the
  aggregate root, shared API resource, and editor composition.

### E1-COORD-VERSION-001 — Immutable snapshot boundary

- Stories: `1-8-create-and-view-an-immutable-cv-version` and downstream Stories
  2.4, 3.1, 4.1, and 4.2.
- Decision owner: `unassigned`.
- Resolution: `pending E1-DEC-006`.
- Reserved boundary: Version migration/model, snapshot service/schema,
  serialization, list ordering, and immutability fixtures.
- Sequence/merge rule: after the Profile schema contract is frozen, Story 1.8
  tasks 01–02 may establish fixtures and persistence alongside Stories 1.4–1.7.
  Their Version-regression checks wait for that checkpoint; Story 1.8 owns
  Version creation/read and downstream stories cannot reinterpret snapshots.

### E1-COORD-TEST-001 — Shared frontend and E2E tooling

- Stories: all Epic 1 stories requiring component or browser verification.
- Decision owner: `unassigned`.
- Resolution: `pending E1-DEC-008`.
- Reserved boundary: shared Vitest/Playwright dependencies, configuration,
  reusable auth/data fixtures, CI command, and disposable database reset.
- Sequence/merge rule: the enablement item lands before dependent verification
  tasks enter `doing`; no story installs a competing harness.

## Discovered work outside Epic story scope

### DISCOVERY-E1-001 — Shared frontend verification enablement

- Status: `unassigned`.
- Owner: `unassigned`.
- Scope: add and configure the project-level Vitest/Playwright harness,
  reusable fixtures, disposable MySQL orchestration, commands, and CI entry.
- Reason externalized: the capability is reusable across all Epics and is not
  independently valuable registration/Profile/Version behavior.
- Blocks: every task referencing `E1-COORD-TEST-001`.
- Acceptance: the owning planning item defines one implementation owner,
  branch/worktree, file boundary, safe database reset contract, and commands
  before any dependent task enters `doing`.
