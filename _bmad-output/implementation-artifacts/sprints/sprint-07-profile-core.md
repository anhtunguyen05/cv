---
sprint_id: sprint-07-profile-core
title: Profile Core
status: draft
start: null
end: null
facilitator: unassigned
goal: Deliver and independently verify the owned mutable Profile aggregate and its create/retrieve journey for Story 1.3.
refinement_stories:
  - 1-3-create-a-cv-profile
committed_stories: []
capacity_assumptions:
  - One integration owner controls the Profile aggregate boundary; review capacity must include database and browser-contract review.
constraints:
  - The charter remains draft until facilitator, dates, and capacity are confirmed; no Story lifecycle changes are implied.
  - Shared Profile migration, policy, API resource, editor state, and fixture changes are serialized through `E1-COORD-PROFILE-001`.
---

# Sprint 07: Profile Core

## Outcome and done signal

Deliver Profile creation, retrieval, owner isolation, validation, revision-aware
writes, and the first reusable Profile fixture corpus against the approved
Profile v1 contract.

The measurable done signal is evidence that a signed-in owner can create and
reload a Profile, that malformed/foreign access is non-disclosing, and that
the aggregate and fixture contract are safe for the section sprints to extend.

## Capacity and dates

Dates remain unset. The sprint is sized only after the Profile integration owner
and the shared-harness availability are confirmed.

## Entry gates

- `profile-v1` and the governing decisions are approved; no data-shape decision
  remains open.
- Story 1.3 consumes the completed account boundary and disposable test harness
  from Sprint 06.
- Name the `E1-COORD-PROFILE-001` integration owner and branch/worktree before
  the shared persistence or fixture corpus changes.

## Dependencies and sequence

Depends on Sprint 06 for the authenticated User boundary and test harness. The
Profile fixture corpus is the first executable artifact; the aggregate,
persistence/API baseline, create/retrieve UI, and cross-layer evidence consume
it without local variants. The result is the prerequisite for the CV section
and immutable Version slices.

## Delivery evidence gates

1. Disposable PostgreSQL evidence covers the ten-Profile quota race,
   normalized-title collision, idempotency replay/mismatch, and rollback with
   no orphan Profile or ledger record.
2. Two-user API and browser checks compare absent, malformed, and foreign
   Profile IDs for status, error body/code, cache headers, side effects, and
   non-disclosure.
3. A fixture revision changes only through contract review, and Laravel, Vue,
   and browser consumers validate the same version before integration.

All membership is recorded in this charter frontmatter. No Story lifecycle or task lifecycle is copied here. An external issue tracker may mirror permanent task keys under the mapping and verification rules in this directory README.
