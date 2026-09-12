---
story_key: 2-4-generate-a-match-report
title: Generate a Match Report
type: feature
created: 2026-09-12
story_owner: unassigned
depends_on_stories:
  - 1-8-create-and-view-an-immutable-cv-version
  - 2-3-analyze-a-job-description
source_story: _bmad-output/planning-artifacts/epics.md
source_story_key: 2-4-generate-a-match-report
review_loop_iteration: 0
context:
  - _bmad-output/planning-artifacts/epics/epic-2-job-fit/README.md
  - _bmad-output/planning-artifacts/epics/epic-2-job-fit/contracts.md
  - _bmad-output/planning-artifacts/epics/epic-2-job-fit/test-strategy.md
  - _bmad-output/planning-artifacts/epics/epic-2-job-fit/decisions.md
---

# Story 2.4: Generate a Match Report

**Lifecycle:** Read only from `_bmad-output/implementation-artifacts/sprint-status.yaml`.

**Planning blockers:** `E2-PREREQ-VERSION-001`, approved
`E2-COORD-ANALYSIS-001`, `E2-DEC-001`, `E2-DEC-004` through
`E2-DEC-007`, `E2-DEC-009`, `DISCOVERY-E2-001`, and
`E2-COORD-MATCH-001`.

## Package map

| File | Purpose |
| --- | --- |
| [Requirements](requirements.md) | Matching behavior, source preconditions, backend, security, UI, and edge cases |
| [Contract](contract.md) | Match request/report response/status/error and FE/API responsibilities |
| [Tasks](tasks.md) | Atomic task board, dependencies, scopes, owners, and coordination |
| [Verification](verification.md) | Six-AC traceability, quality fixtures, exit gate, and evidence rules |

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

**Problem:** A User cannot reliably measure how one saved CV Version fits a
specific current target-role source.

**Approach:** Deterministically compare exactly one owned immutable CV Version
with the successful Analysis of one owned current active Job Description
revision and persist an immutable, explainable, versioned Match Report.

</frozen-after-approval>

## References

- Global: `AD-1` through `AD-5`, `AD-11`, `AD-14` through `AD-19`, Global
  API/error/security/data/reliability/accessibility/testing standards.
- Epic 1: `E1-CONTRACT-VERSION-001`, `E1-COORD-VERSION-001`.
- Epic 2: `E2-BR-001`, `E2-BR-005`, `E2-BR-007` through `E2-BR-020`,
  `E2-CONTRACT-ANALYSIS-001`, `E2-CONTRACT-MATCH-001`,
  `E2-CONTRACT-ERROR-001`, `E2-DATA-003`, `E2-COORD-ANALYSIS-001`,
  `E2-COORD-MATCH-001`, and `E2-COORD-TEST-001`.

## Canonical Acceptance Criteria

- **AC-2-4-generate-a-match-report-01:** Given I own a CV Version and a
  non-deleted Job Description whose current revision has successful Analysis,
  when I request comparison, then the system creates a Match Report from only
  that current revision; includes overall score, matched skills, missing skills,
  Weak Evidence, and recommendations; and stores the exact revision, Analysis,
  `analysis_id`, and matching-rule version used.
- **AC-2-4-generate-a-match-report-02:** Given the current non-deleted Job
  Description revision has no successful Analysis, when I request a Match
  Report, then the request is rejected with an actionable Analyze-first state
  and no partial Match Report is created.
- **AC-2-4-generate-a-match-report-03:** Given an older revision has successful
  Analysis and a newer revision is current, when I request a new Match Report,
  then the old Analysis is not reused and the request succeeds only after the
  current revision has successful Analysis.
- **AC-2-4-generate-a-match-report-04:** Given I select a historical revision or
  logically deleted Job Description for a new Match Report, when requested,
  then the system rejects it while leaving preserved historical Analysis and
  existing Match Reports readable.
- **AC-2-4-generate-a-match-report-05:** Given the CV Version or Job Description
  belongs to another User, when I request comparison, then access is denied and
  no Match Report is created.
- **AC-2-4-generate-a-match-report-06:** Given CV Version, Job Description, and
  matching-rule version have not changed, when I repeat comparison, then the
  report result is the same.

## Readiness coverage

The package covers exact-source resolution, current/analysis/deleted/history
preconditions, mixed ownership, deterministic scoring/evidence/recommendations,
rule versions, repeated/concurrent requests, stale source races, no partial
report, safe UX, frontend adapters, MySQL persistence, quality fixtures, and
E2E generation. Exact weights, thresholds, rounding, aliases, explanation
shape, request dedupe, timeout, and quality approval remain open.

## Code map

- Laravel deterministic matcher/evidence resolver, Match Report aggregate,
  persistence, ownership policies, application transaction, and API resources.
- Epic 1 CV Version and Story 2.3 Analysis are read-only source contracts.
- Vue Match Report adapter/query/mutation, source selection, Analyze-first,
  conflict, success/navigation, and safe error states.
- Versioned evaluation corpus plus PHPUnit/MySQL/Vitest/Playwright verification.

## Spec change log

- 2026-09-12: Initial Story package created from canonical Story 2.4.
