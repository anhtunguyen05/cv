---
story_key: 2-5-review-an-explainable-match-report
title: Review an explainable Match Report
type: feature
created: 2026-09-12
story_owner: unassigned
depends_on_stories:
  - 2-4-generate-a-match-report
source_story: _bmad-output/planning-artifacts/epics.md
source_story_key: 2-5-review-an-explainable-match-report
review_loop_iteration: 0
context:
  - _bmad-output/planning-artifacts/epics/epic-2-job-fit/README.md
  - _bmad-output/planning-artifacts/epics/epic-2-job-fit/contracts.md
  - _bmad-output/planning-artifacts/epics/epic-2-job-fit/ux-and-validation.md
  - _bmad-output/planning-artifacts/epics/epic-2-job-fit/decisions.md
---

# Story 2.5: Review an explainable Match Report

**Lifecycle:** Read only from `_bmad-output/implementation-artifacts/sprint-status.yaml`.

**Planning blockers:** approved `E2-COORD-MATCH-001` checkpoint from Story 2.4,
`E2-PREREQ-VERSION-001`, `E2-DEC-001`, `E2-DEC-005`, `E2-DEC-006`,
`E2-DEC-008`, `E2-DEC-009`, and `DISCOVERY-E2-001`.

## Package map

| File | Purpose |
| --- | --- |
| [Requirements](requirements.md) | Explanation, source integrity, security, accessibility, UI, and edge cases |
| [Contract](contract.md) | Report list/detail projection and FE/API responsibilities |
| [Tasks](tasks.md) | Atomic read/projection/UI/test task board and dependencies |
| [Verification](verification.md) | Four-AC traceability, accessibility, truthfulness, and exit evidence |

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

**Problem:** A score without sources and evidence categories cannot tell a User
what is supported, missing, weak, or safe to improve.

**Approach:** Present the stored immutable Match Report with exact CV/JD/Analysis
sources, semantic matched/missing/Weak Evidence groups, and actionable section
references in an accessible reading order without recomputing or inventing data.

</frozen-after-approval>

## References

- Global: `AD-2`, `AD-4`, `AD-5`, `AD-11`, `AD-14` through `AD-19`, Global
  API/error/security/data/reliability/accessibility/testing standards.
- Epic 1: `E1-CONTRACT-VERSION-001`, `E1-COORD-VERSION-001`.
- Epic 2: `E2-BR-001`, `E2-BR-006`, `E2-BR-011` through `E2-BR-017`,
  `E2-BR-019`, `E2-BR-020`, `E2-CONTRACT-MATCH-001`,
  `E2-CONTRACT-ERROR-001`, `E2-DATA-003`, `E2-COORD-MATCH-001`, and
  `E2-COORD-TEST-001`.

## Canonical Acceptance Criteria

- **AC-2-5-review-an-explainable-match-report-01:** Given I own a Match Report,
  when I open it, then I see the source CV Version and Job Description, the exact
  Job Description revision, distinct matched/missing/Weak Evidence groups, and
  each available recommendation's related CV section.
- **AC-2-5-review-an-explainable-match-report-02:** Given a skill appears only in
  the CV skills list without supporting project or experience Evidence, when the
  report is displayed, then it appears as Weak Evidence rather than fully
  evidenced.
- **AC-2-5-review-an-explainable-match-report-03:** Given the report has no match
  for a requested signal, when viewed, then the system does not replace the
  missing signal with an unsupported claim.
- **AC-2-5-review-an-explainable-match-report-04:** Given I navigate with a
  keyboard or assistive technology, when I review score, skills, and
  recommendations, then content has meaningful labels and usable reading/order
  sequence.

## Readiness coverage

The package covers owned immutable read/list, exact source and deleted-parent
context, stored-not-recomputed output, empty classifications, Weak Evidence,
unsupported-claim prevention, recommendation section references, safe
rendering, score semantics, loading/error states, keyboard/assistive order,
component tests, and E2E review. Exact explanation schema, disclaimer, ordering,
pagination, source summary, and quality evidence remain open decisions.

## Code map

- Laravel Match Report read/list query, policy, resource projection, and source
  summary using stored immutable data.
- Vue Match Report adapter/query plus accessible score/source/classification/
  recommendation components and deleted-source context.
- Component/a11y/contract and Playwright review journeys.

## Spec change log

- 2026-09-12: Initial Story package created from canonical Story 2.5.
