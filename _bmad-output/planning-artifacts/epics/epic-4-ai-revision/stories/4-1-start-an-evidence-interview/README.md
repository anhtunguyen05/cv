---
story_key: 4-1-start-an-evidence-interview
title: Start an Evidence interview
type: feature
created: 2026-09-12
story_owner: unassigned
depends_on_stories:
  - 2-5-review-an-explainable-match-report
source_story: _bmad-output/planning-artifacts/epics.md
source_story_key: 4-1-start-an-evidence-interview
review_loop_iteration: 0
context:
  - _bmad-output/planning-artifacts/epics/epic-4-ai-revision/README.md
  - _bmad-output/planning-artifacts/epics/epic-4-ai-revision/contracts.md
  - _bmad-output/planning-artifacts/epics/epic-4-ai-revision/decisions.md
---

# Story 4.1: Start an Evidence interview

**Lifecycle:** Read only from `_bmad-output/implementation-artifacts/sprint-status.yaml`.

**Planning blockers:** `E4-PREREQ-VERSION-001`, `E4-PREREQ-MATCH-001`,
`E4-DEC-001`, `E4-DEC-002`, `E4-DEC-009`, `E4-COORD-INTERVIEW-001`, and
`E4-COORD-TEST-001`.

## Package map

| File | Purpose |
| --- | --- |
| [Requirements](requirements.md) | Eligibility, source/domain rules, security, validation, UX, integration, edge cases |
| [Contract](contract.md) | Start/read request, response, status/error, and FE/API responsibilities |
| [Tasks](tasks.md) | Atomic tasks, dependencies, declared scopes, blockers, and coordination |
| [Verification](verification.md) | AC traceability, evidence layers, and exit gate |

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

Start a bounded Evidence session only when an owned immutable Match Report has
real unresolved improvement areas. Pin all sources and leave the source CV
Version unchanged.

</frozen-after-approval>

## Canonical Acceptance Criteria

- **AC-4-1-start-an-evidence-interview-01:** Given I own a Match Report with
  missing or Weak Evidence areas, when I start an interview, then one session is
  associated with that report, CV Version, and Job Description, identifies the
  required Evidence areas, and leaves the source Version unchanged.
- **AC-4-1-start-an-evidence-interview-02:** Given the report has no unresolved
  area, when I try to start, then the system explains no interview is needed and
  creates no misleading session.

## Readiness coverage

Covers eligibility, exact source tuple, area/question snapshot, repeated/lost
start, active-session reuse/restart, ownership, no-op state, API/UI integration,
accessibility, transaction, fixtures, and E2E. Exact eligibility and session
lifecycle remain open in `E4-DEC-002`.

## References

- Epic: `E4-BR-001` through `E4-BR-007`, `E4-CONTRACT-INTERVIEW-001`,
  `E4-CONTRACT-ERROR-001`, `E4-DATA-001`, `E4-SEC-001`,
  `E4-COORD-INTERVIEW-001`, `E4-COORD-TEST-001`.
- Code map: `apps/api/app/`, `routes/api.php`, `apps/web/src/`; shared schema and
  fixture paths are assigned at coordination checkpoint.
