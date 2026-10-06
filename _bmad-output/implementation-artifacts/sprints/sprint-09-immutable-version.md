---
sprint_id: sprint-09-immutable-version
title: Immutable CV Version
status: draft
start: null
end: null
facilitator: unassigned
goal: Deliver and independently verify immutable named CV Version snapshots and owner-only reads for Story 1.8.
refinement_stories:
  - 1-8-create-and-view-an-immutable-cv-version
committed_stories: []
capacity_assumptions:
  - One Version integration owner controls the snapshot service, migration, and immutability guard; review capacity includes PostgreSQL constraint verification.
constraints:
  - The charter remains draft until facilitator, dates, and capacity are confirmed; no Story lifecycle changes are implied.
  - Version behavior is governed exclusively by Version v1; no update/delete route or live-Profile reconstruction is permitted.
---

# Sprint 09: Immutable CV Version

## Outcome and done signal

Deliver transactionally created named snapshots, deterministic owner-only list and
detail reads, and database-enforced immutability using the approved Version v1
contract.

The measurable done signal is proof that a Version captures exactly one locked
Profile revision, later Profile edits cannot affect it, retries do not duplicate
it, foreign access is non-disclosing, and update/delete attempts are rejected.

## Capacity and dates

Dates remain unset. The sprint is sized after the Version integration owner and
the Sprint 07 Profile persistence baseline are available.

## Entry gates

- Profile v1 and Version v1 are approved; no snapshot decision remains open.
- Sprint 07 supplies the Profile aggregate/persistence baseline and Sprint 06
  supplies the disposable verification harness. The complete supported section
  shape is already fixed by Profile v1, so Sprint 09 may run alongside Sprint
  08 once Sprint 07 is accepted.
- Name the `E1-COORD-VERSION-001` integration owner and branch/worktree before
  the shared snapshot fixture, migration, or service changes.

## Dependencies and sequence

Depends on Sprint 07's Profile aggregate and Sprint 06's harness. The Version
integration owner establishes the snapshot persistence and fixture boundary
first. Sprint 08 uses that fixture for its final Version-regression evidence;
Version endpoint work may continue in parallel. Create, list, detail, and
immutability evidence consume the same boundary. This contract is consumed by
later Job Fit, Preview/Export, and Evidence work.

## Delivery evidence gates

1. Disposable PostgreSQL checks directly attempt Version `UPDATE` and `DELETE`
   through the application database role, verify trigger rejection and
   Profile `ON DELETE RESTRICT`, and compare the RFC 8785 snapshot hash.
2. Integration tests cover both Profile/Version lock orders: an earlier update
   makes Version creation stale; an earlier Version captures exactly one source
   revision while the update waits. They also prove idempotent replay returns
   the original Version.
3. List checks cover tied `created_at` values ordered by `id DESC`, page
   boundaries and `per_page` limits, owned/foreign/malformed `profile_id`, and
   pagination links retaining every filter.

All membership is recorded in this charter frontmatter. No Story lifecycle or task lifecycle is copied here. An external issue tracker may mirror permanent task keys under the mapping and verification rules in this directory README.
