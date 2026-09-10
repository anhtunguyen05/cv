---
story_key: 1-3-create-a-cv-profile
title: Create a CV Profile
type: feature
created: 2026-09-10
status: draft
story_owner: unassigned
depends_on_stories:
  - 1-2-sign-in-and-sign-out
source_story: _bmad-output/planning-artifacts/epics.md
source_story_key: 1-3-create-a-cv-profile
review_loop_iteration: 0
context:
  - _bmad-output/planning-artifacts/epics/epic-1-trusted-cv/README.md
  - _bmad-output/planning-artifacts/epics/epic-1-trusted-cv/business-rules.md
  - _bmad-output/planning-artifacts/epics/epic-1-trusted-cv/contracts.md
  - _bmad-output/planning-artifacts/epics/epic-1-trusted-cv/decisions.md
---

# Story 1.3: Create a CV Profile

**Readiness:** Draft; blocked by `E1-DEC-003` through `E1-DEC-005`,
`E1-DEC-007`, `E1-DEC-008`, and `E1-COORD-PROFILE-001`.

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

**Problem:** An authenticated User has no owned structured source for their CV.

**Approach:** Create one valid mutable CV Profile aggregate with title and
approved personal-information fields, then retrieve the same owned resource.

## References

- Global: `AD-1` through `AD-3`, `AD-14` through `AD-19`,
  `API-STD-001` through `API-STD-007`, `HTTP-CONTRACT-001` through
  `HTTP-CONTRACT-006`, `SEC-STD-003` through `SEC-STD-006`,
  `DATA-STD-001` through `DATA-STD-008`, `VAL-STD-001` through
  `VAL-STD-005`, `A11Y-STD-001` through `A11Y-STD-003`, and
  `TEST-STD-001` through `TEST-STD-005`.
- Epic: `E1-BR-001` through `E1-BR-011`, `E1-CONTRACT-PROFILE-001`,
  `E1-CONTRACT-ERROR-001`, `E1-STATE-PROFILE-001`, `E1-DATA-002` through
  `E1-DATA-007`, `E1-SEC-004` through `E1-SEC-008`,
  `E1-COORD-PROFILE-001`, `E1-COORD-TEST-001`.

## Boundaries & Constraints

**Always:** Derive ownership from the session; validate the whole requested
aggregate; use a server ULID; persist atomically; return the shared Profile
shape; preserve valid form values on failure.

**Ask First:** Freeze Profile/personal fields, limits, endpoint/write shape,
persistence split, concurrency token, first Profile route and test tooling.

**Never:** Accept owner IDs, create partial Profiles, add Profile deletion,
create a Version, or disclose cross-user existence.

## Story-specific rules and edge cases

- **PROFILE-CREATE-BR-001:** A create request yields one Profile or no Profile.
- **PROFILE-CREATE-BR-002:** A newly created Profile initializes unsupported or
  optional sections only according to the shared schema, not ad-hoc defaults.

| Scenario | Expected behavior |
| --- | --- |
| Valid title/personal data | Owned Profile created and reloadable |
| Empty/over-limit title or invalid field | Field errors; no partial Profile |
| Duplicate submit/lost response | Reconcile approved create outcome before retry |
| Cross-user ID access | Non-disclosing not found; no mutation |
| Expired auth | Protected data cleared and create rejected |

</frozen-after-approval>

## Canonical Acceptance Criteria

- **AC-1-3-create-a-cv-profile-01:** Given I am authenticated, when I create a
  CV Profile with a valid title and personal information, then it is created
  for me, reloadable, and belongs only to me.
- **AC-1-3-create-a-cv-profile-02:** Given an empty or over-limit title, when I
  create the Profile, then the request has a field error and no partial Profile.
- **AC-1-3-create-a-cv-profile-03:** Given invalid personal-information types or
  values, when I create the Profile, then invalid fields are rejected and valid
  unrelated data is not persisted as a partial record.
- **AC-1-3-create-a-cv-profile-04:** Given another User accesses the Profile by
  ID, when requested, then the Profile is not disclosed and remains unchanged.
- **AC-1-3-create-a-cv-profile-05:** Given invalid/incomplete form data, when
  validation fails, then relevant controls expose keyboard-usable errors and
  valid entered values remain.

## Readiness coverage

Create/reload, invalid, cross-user, duplicate and expiry behavior are covered.
Shared Profile/error contracts, aggregate transaction, ownership, layered
validation, accessible form states, API mapping, and all test layers are
referenced; exact schema and concurrency remain open decisions.

## Code Map

- `apps/api/app/`, `database/migrations/` — Profile aggregate, repository,
  policy, transaction and ULID persistence.
- `apps/api/routes/api.php`, Presentation HTTP boundary — create/detail contract.
- `apps/web/src/features/cv-profiles/`, `pages/`, `app/router/` — API/schema/form.
- API/web/E2E test locations — planned verification.

## Tasks & Acceptance

**Execution:**

- [ ] TASK-1-3-01: Add executable Profile and personal-information fixtures
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `none`
  - Covers: `AC-1-3-create-a-cv-profile-01` through `AC-1-3-create-a-cv-profile-05`
  - Scope: stable Profile contract fixtures
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: `E1-DEC-003` through `E1-DEC-005`
  - Outcome: Add executable Profile and personal-information fixtures.
  - Acceptance: fixtures freeze fields, paths, errors, ownership and create/reload shapes.
  - Verification: schema validation and rule/AC review.
- [ ] TASK-1-3-02: Implement Profile aggregate and persistence foundation
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-3-01`
  - Covers: `AC-1-3-create-a-cv-profile-01` through `AC-1-3-create-a-cv-profile-04`
  - Scope: domain/application, migration/model/repository/transaction
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: `none`
  - Outcome: Implement Profile aggregate and persistence foundation.
  - Acceptance: owned ULID Profile creates atomically with approved schema.
  - Verification: unit and disposable-MySQL migration/persistence tests.
- [ ] TASK-1-3-03: Implement Profile authorization policy
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-3-02`
  - Covers: `AC-1-3-create-a-cv-profile-01`, `AC-1-3-create-a-cv-profile-04`
  - Scope: policy/query ownership and non-disclosure
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: `1-2-sign-in-and-sign-out` or approved `E1-COORD-AUTH-001` checkpoint
  - Outcome: Implement Profile authorization policy.
  - Acceptance: cross-user and missing IDs are externally identical.
  - Verification: policy and feature tests with two Users.
- [ ] TASK-1-3-04: Expose Profile create and detail API
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-3-02`, `TASK-1-3-03`
  - Covers: `AC-1-3-create-a-cv-profile-01` through `AC-1-3-create-a-cv-profile-04`
  - Scope: Form Request, controller/resource/routes/errors
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: `none`
  - Outcome: Expose Profile create and detail API.
  - Acceptance: contract fixtures pass with no partial or sensitive state.
  - Verification: Laravel feature and MySQL integration tests.
- [ ] TASK-1-3-05: Implement Profile create frontend adapter and form
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-3-01`
  - Covers: `AC-1-3-create-a-cv-profile-01` through `AC-1-3-create-a-cv-profile-03`, `AC-1-3-create-a-cv-profile-05`
  - Scope: feature API/schema/mutation/page/form
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: `E1-DEC-007`
  - Outcome: Implement Profile create frontend adapter and form.
  - Acceptance: accessible create/pending/error/reconcile/success states preserve valid values.
  - Verification: type-check and component tests after enablement.
- [ ] TASK-1-3-06: Verify Profile create backend and integration
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-3-04`
  - Covers: `AC-1-3-create-a-cv-profile-01` through `AC-1-3-create-a-cv-profile-05`
  - Scope: unit/feature/MySQL suite
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: `none`
  - Outcome: Verify Profile create backend and integration.
  - Acceptance: atomicity, reload, ownership, malformed and concurrency evidence pass.
  - Verification: focused PHPUnit suites on declared databases.
- [ ] TASK-1-3-07: Verify Profile create end to end
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-3-05`, `TASK-1-3-06`
  - Covers: `AC-1-3-create-a-cv-profile-01` through `AC-1-3-create-a-cv-profile-05`
  - Scope: browser create/reload/error/cross-user journey
  - Coordination: `E1-COORD-TEST-001`
  - Blocked by: `E1-DEC-008`
  - Outcome: Verify Profile create end to end.
  - Acceptance: critical path and meaningful failures pass on disposable data.
  - Verification: approved Playwright command.

## Dependency and concurrency map

```text
01 -> {02,05}; 02 -> 03; {02,03} -> 04 -> 06; {05,06} -> 07
```

## Coordination and verification gate

Use `E1-COORD-AUTH-001` for the session boundary,
`E1-COORD-PROFILE-001` for aggregate/API/editor files, and
`E1-COORD-TEST-001` for browser tooling. Tasks 06–07 gate all five ACs.

## Spec Change Log

- 2026-09-10: Initial Epic-wide draft created from canonical Story 1.3.
