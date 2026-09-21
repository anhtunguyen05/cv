---
sprint_id: sprint-06-account-access
title: Account Access
status: draft
start: null
end: null
facilitator: unassigned
goal: Canonical account registration and sign-in acceptance criteria have complete task and verification coverage, with authentication decisions and the shared auth checkpoint explicitly owned.
refinement_stories:
  - 1-1-register-an-account
  - 1-2-sign-in-and-sign-out
committed_stories: []
capacity_assumptions:
  - One developer is assumed for this planning slice; available time and throughput have not been measured, so no calendar duration is forecast.
constraints:
  - Draft refinement only; all listed Stories remain backlog and no implementation commitment is implied.
  - Keep unresolved decisions and prerequisites as Story-specific blockers; do not mark Stories ready-for-dev or committed.
---

# Sprint 06: Account Access

## Outcome and done signal

Canonical account registration and sign-in acceptance criteria have complete task and verification coverage, with authentication decisions and the shared auth checkpoint explicitly owned.

The measurable done signal is 100% of canonical acceptance criteria mapped to task and verification coverage; no unreviewed BMAD findings remain unless recorded as named decisions; and every unresolved prerequisite remains visible as a blocker attached to its Story. The sprint stays a draft until scheduling and readiness decisions are made.

## Capacity and dates

One developer is assumed. Dates remain unset, and this forecast does not claim a velocity or duration.

## Story-specific blockers

- **1-1-register-an-account — Register an account:** `E1-DEC-001`, `E1-DEC-002`, `E1-DEC-007` through `E1-DEC-009` block readiness approval. No implementation is authorized.
- **1-2-sign-in-and-sign-out — Sign in and sign out:** `E1-DEC-001`, `E1-DEC-002`, `E1-DEC-007` through `E1-DEC-009`, and the approved `E1-COORD-AUTH-001` checkpoint.

## Dependencies and sequence

This is the first replacement delivery slice; it supersedes the account-access portion of the former Epic 1 refinement charter.

Story 1.2 follows Story 1.1. They share account, session, and current-account contracts. Keep both in refinement until the listed decisions and E1-COORD-AUTH-001 checkpoint are resolved.

All membership is recorded in this charter frontmatter. No Story lifecycle or task lifecycle is copied here. An external issue tracker may mirror permanent task keys under the mapping and verification rules in this directory README.
