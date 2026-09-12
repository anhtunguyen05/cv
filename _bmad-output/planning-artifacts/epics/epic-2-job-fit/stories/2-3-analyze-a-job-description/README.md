---
story_key: 2-3-analyze-a-job-description
title: Analyze a Job Description
type: feature
created: 2026-09-12
story_owner: unassigned
depends_on_stories:
  - 2-1-save-a-job-description
source_story: _bmad-output/planning-artifacts/epics.md
source_story_key: 2-3-analyze-a-job-description
review_loop_iteration: 0
context:
  - _bmad-output/planning-artifacts/epics/epic-2-job-fit/README.md
  - _bmad-output/planning-artifacts/epics/epic-2-job-fit/contracts.md
  - _bmad-output/planning-artifacts/epics/epic-2-job-fit/test-strategy.md
  - _bmad-output/planning-artifacts/epics/epic-2-job-fit/decisions.md
---

# Story 2.3: Analyze a Job Description

**Lifecycle:** Read only from `_bmad-output/implementation-artifacts/sprint-status.yaml`.

**Planning blockers:** approved `E2-COORD-JD-001` checkpoint,
`E2-DEC-001` through `E2-DEC-004`, `E2-DEC-007`, `E2-DEC-009`, and
`E2-COORD-ANALYSIS-001`.

## Package map

| File | Purpose |
| --- | --- |
| [Requirements](requirements.md) | Deterministic extraction behavior, backend, security, validation, UI, and edge cases |
| [Contract](contract.md) | Analyze/read request, result, version, status/error, and FE/API responsibilities |
| [Tasks](tasks.md) | Atomic task board, dependencies, scopes, owners, and coordination |
| [Verification](verification.md) | AC traceability, deterministic fixtures, exit gate, and evidence rules |

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

**Problem:** Raw Job Description text alone does not clearly expose the target
role signals a User needs before comparing a CV.

**Approach:** Run a deterministic, provider-free interpretation of one exact
current immutable revision, store the validated result separately, and show
source-derived signals with explicit absent/unknown state.

</frozen-after-approval>

## References

- Global: `AD-2`, `AD-4`, `AD-5`, `AD-11`, `AD-14` through `AD-19`,
  Global API/error/security/data/reliability/accessibility/testing standards.
- Epic: `E2-BR-001`, `E2-BR-003`, `E2-BR-005`, `E2-BR-007` through
  `E2-BR-010`, `E2-BR-017` through `E2-BR-020`,
  `E2-CONTRACT-JD-REVISION-001`, `E2-CONTRACT-ANALYSIS-001`,
  `E2-CONTRACT-ERROR-001`, `E2-DATA-002`, `E2-COORD-JD-001`,
  `E2-COORD-ANALYSIS-001`, and `E2-COORD-TEST-001`.

## Canonical Acceptance Criteria

- **AC-2-3-analyze-a-job-description-01:** Given I own a saved Job Description
  with valid raw text, when I request Analysis, then the system extracts
  available role, required skills, nice-to-have skills, responsibilities,
  keywords, seniority, soft skills, and domain/context signals; stores the
  Analysis separately; and identifies the exact revision and analysis-rule
  version used.
- **AC-2-3-analyze-a-job-description-02:** Given a supported signal type is not
  present, when Analysis completes, then that signal is absent or unknown and
  the system does not invent a value.
- **AC-2-3-analyze-a-job-description-03:** Given the same raw Job Description is
  analyzed with the same rule version, when repeated, then the extracted result
  is repeatable.
- **AC-2-3-analyze-a-job-description-04:** Given Analysis cannot complete, when
  failure is returned, then the saved raw text remains intact, the User receives
  an actionable retry state, and no incomplete Analysis appears successful.

## Readiness coverage

The package covers all eight signal groups, absent/unknown semantics, rule and
schema versions, repeatability, duplicated/concurrent requests, stale/deleted
sources, explicit retry/terminal failure, safe logs/rendering, frontend source
distinction, versioned corpora, MySQL persistence, and E2E analysis. Exact
vocabulary, aliases, ordering, deterministic key, timeout/rate limit, and
quality ownership remain open decisions.

## Code map

- Laravel deterministic analysis domain/application service, schema validator,
  repository, policy, and `/api/v1` presentation boundary.
- Job Description revision model is consumed read-only from Story 2.1.
- Vue analysis adapter/query/mutation, raw-versus-derived review surface, retry
  and stale-revision state.
- Versioned fixture corpus plus PHPUnit/MySQL/Vitest/Playwright checks.

## Spec change log

- 2026-09-12: Initial Story package created from canonical Story 2.3.
