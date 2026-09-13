---
story_key: 2-1-save-a-job-description
title: Save a Job Description
type: feature
created: 2026-09-12
story_owner: unassigned
depends_on_stories:
  - 1-2-sign-in-and-sign-out
source_story: _bmad-output/planning-artifacts/epics.md
source_story_key: 2-1-save-a-job-description
review_loop_iteration: 0
context:
  - _bmad-output/planning-artifacts/epics/epic-2-job-fit/README.md
  - _bmad-output/planning-artifacts/epics/epic-2-job-fit/contracts.md
  - _bmad-output/planning-artifacts/epics/epic-2-job-fit/data-and-lifecycle.md
  - _bmad-output/planning-artifacts/epics/epic-2-job-fit/decisions.md
---

# Story 2.1: Save a Job Description

**Lifecycle:** Read only from `_bmad-output/implementation-artifacts/sprint-status.yaml`.

**Planning blockers:** `E2-PREREQ-AUTH-001`, `E2-DEC-001` through
`E2-DEC-003`, `E2-DEC-007` through `E2-DEC-009`, and `E2-COORD-JD-001`.

## Package map

| File | Purpose |
| --- | --- |
| [Requirements](requirements.md) | Behavior, backend/domain rules, security, validation, frontend, integration, and edge cases |
| [Contract](contract.md) | Create/read request, response, status/error, and FE/API responsibilities |
| [Tasks](tasks.md) | Atomic task board, dependencies, scopes, owners, and coordination |
| [Verification](verification.md) | AC traceability, test layers, exit gate, and evidence rules |

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

**Problem:** An authenticated User cannot preserve a target role source for
later analysis and CV comparison.

**Approach:** Save bounded pasted Job Description text plus optional role/company
metadata as one owned logical resource with an atomic immutable initial revision.

</frozen-after-approval>

## References

- Global: `AD-2`, `AD-4`, `AD-11`, `AD-13` through `AD-19`,
  `API-STD-001` through `API-STD-007`, `SEC-STD-003` through `SEC-STD-006`,
  `DATA-STD-001` through `DATA-STD-008`, `VAL-STD-001` through
  `VAL-STD-005`, `REL-STD-001` through `REL-STD-005`, and `TEST-STD-001`
  through `TEST-STD-007`.
- Epic: `E2-BR-001` through `E2-BR-006`, `E2-BR-017` through
  `E2-BR-020`, `E2-CONTRACT-JD-001`, `E2-CONTRACT-JD-REVISION-001`,
  `E2-CONTRACT-ERROR-001`, `E2-DATA-001`, `E2-COORD-JD-001`, and
  `E2-COORD-TEST-001`.

## Canonical Acceptance Criteria

- **AC-2-1-save-a-job-description-01:** Given I am authenticated, when I submit
  valid raw Job Description text with optional company and role information,
  then the system saves a Job Description owned by me, preserves the original
  raw text, lets me retrieve it after reloading, and gives it a stable identity
  with an initial immutable revision.
- **AC-2-1-save-a-job-description-02:** Given raw Job Description text is empty
  or exceeds the supported input limit, when I submit it, then the system
  rejects the request with a field-level validation error and saves no partial
  Job Description.
- **AC-2-1-save-a-job-description-03:** Given another User attempts to access my
  Job Description, when requested, then the system denies access without
  disclosing the resource.

## Readiness coverage

The package covers creation/reload, atomic initial revision, invalid and
oversized input, duplicate/lost submission, authenticated ownership,
non-disclosure, safe text rendering, UI states, FE/API mapping, MySQL
constraints, and browser verification. Exact input, HTTP, concurrency,
deduplication, rate-limit, list/detail, and tooling values remain in the Epic
decision register.

## Code map

- `apps/api/app/`, `database/migrations/` — aggregate, revision, repository,
  policy, application service, request/resource, and transaction.
- `apps/api/routes/api.php` — proposed protected product routes.
- `apps/web/src/shared/api/`, `features/job-descriptions/`, `pages/`, router —
  transport, schema/state, create form, detail/reload, and errors.
- API/MySQL/Vitest/Playwright locations — planned verification only.

## Spec change log

- 2026-09-12: Initial Story package created from canonical Story 2.1.
