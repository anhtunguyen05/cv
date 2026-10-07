---
sprint_id: sprint-11-analysis-and-match-report
title: Analysis and Match Report
status: active
start: 2026-10-07
end: null
facilitator: Codex
goal: Implement and verify deterministic Analysis and explainable Match Reports (Stories 2.3–2.5) on `feat/epic-2-job-fit`.
refinement_stories: []
committed_stories:
  - 2-3-analyze-a-job-description
  - 2-4-generate-a-match-report
  - 2-5-review-an-explainable-match-report
capacity_assumptions:
  - One developer is assumed for this planning slice; available time and throughput have not been measured, so no calendar duration is forecast.
constraints:
  - Disposable PostgreSQL 16, the approved 40+12 quality corpus, and the
    shared Playwright journey are complete automated gates.
  - `DISCOVERY-E2-001` is approved and retained as Epic 5 regression input.
---

# Sprint 11: Analysis and Match Report

## Outcome and done signal

Stories 2.3–2.5 have complete canonical AC-to-task/verification coverage for deterministic current-revision analysis and explainable Match Reports.

The measurable done signal is 100% of canonical acceptance criteria mapped to task and verification coverage; no unreviewed BMAD findings remain unless recorded as named decisions; and every unresolved prerequisite remains visible as a blocker attached to its Story. Automated PostgreSQL/browser gates have passed; the sprint remains active for review and manual accessibility sign-off.

## Capacity and dates

One developer is assumed. Dates remain unset, and this forecast does not claim a velocity or duration.

## Story-specific blockers

- **2-3-analyze-a-job-description — Analyze a Job Description:** approved `E2-COORD-JD-001` checkpoint, `E2-DEC-001` through `E2-DEC-004`, `E2-DEC-007`, `E2-DEC-009`, and `E2-COORD-ANALYSIS-001`.
- **2-4-generate-a-match-report — Generate a Match Report:** `E2-PREREQ-VERSION-001`, approved `E2-COORD-ANALYSIS-001`, `E2-DEC-001`, `E2-DEC-004` through `E2-DEC-007`, `E2-DEC-009`, and `E2-COORD-MATCH-001`.
- **2-5-review-an-explainable-match-report — Review an explainable Match Report:** approved `E2-COORD-MATCH-001` checkpoint from Story 2.4, `E2-PREREQ-VERSION-001`, `E2-DEC-001`, `E2-DEC-005`, `E2-DEC-006`, `E2-DEC-008`, and `E2-DEC-009`.

## Dependencies and sequence

Depends on Sprint 10's approved `E2-COORD-JD-001` revision and
`E2-COORD-ANALYSIS-001` analysis checkpoints, and Sprint 09's approved
`E2-PREREQ-VERSION-001` consumer contract. Match Report review consumes the
approved `E2-COORD-MATCH-001` generation contract.

Keep matching deterministic and versioned. Record the approved quality baseline
as Epic 5 regression input; no Epic 2 quality blocker remains.

All membership is recorded in this charter frontmatter. No Story lifecycle or task lifecycle is copied here. An external issue tracker may mirror permanent task keys under the mapping and verification rules in this directory README.
