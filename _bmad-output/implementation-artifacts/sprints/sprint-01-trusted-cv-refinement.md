---
sprint_id: sprint-01-trusted-cv-refinement
title: Trusted CV Epic Refinement
status: draft
start: null
end: null
facilitator: unassigned
goal: Produce a reviewed Epic 1 shared planning package and atomic draft task breakdowns for all eight Trusted CV stories without changing application code.
refinement_stories:
  - 1-1-register-an-account
  - 1-2-sign-in-and-sign-out
  - 1-3-create-a-cv-profile
  - 1-4-manage-cv-summary-and-skills
  - 1-5-manage-education-and-experience
  - 1-6-manage-projects
  - 1-7-manage-supplementary-cv-sections
  - 1-8-create-and-view-an-immutable-cv-version
committed_stories: []
capacity_assumptions:
  - Team dates, facilitator, and available planning capacity are not yet confirmed.
constraints:
  - Planning artifacts only; no application, infrastructure, dependency, test, or runtime changes.
  - Story lifecycle remains backlog until the draft is approved and published by the sprint integration owner.
---

# Sprint 01: Trusted CV Epic Refinement

## Goal and done signal

The sprint goal is to turn the eight canonical Epic 1 stories into a coherent,
reviewed planning package that a team can implement without duplicating shared
rules or inventing product, security, data, API, UX, or verification decisions.

This planning sprint is complete only when the Epic 1 package and all eight
story drafts have passed BMAD review; every material decision has an owner,
approved resolution, and dated approval evidence; the team has accepted the
cross-story dependencies and coordination boundaries; and the sprint
integration owner has published approved stories in dependency order,
synchronized their lifecycle through BMAD, and validated the resulting tracker.

Opening the draft planning PR completes the current breakdown assignment, but
does not complete this sprint or authorize implementation.

## Readiness blockers

- Sprint dates: unassigned.
- Facilitator: unassigned.
- Team planning capacity and absences: unconfirmed.
- Sprint integration owner: unassigned; required before publication or
  lifecycle synchronization.
- Epic 1 product/security/data/contract decisions: open in the Epic decision
  register.

The charter must remain `draft` until those values are supplied and the
pre-commit charter checks in `docs/sprint-workflow.md` pass. A zero-length
`committed_stories` list cannot by itself trigger `review` or `closed`; the
accepted planning outcome and integration evidence above are required.

## Selection rationale

Epic 1 establishes both account access and the trusted structured CV source
needed by every later Epic. Refining all eight stories together exposes shared
User/session, Profile aggregate/editor, and immutable Version boundaries before
individual tasks are assigned. Stories 1.4 through 1.7 remain independently
assignable after the shared Profile checkpoint is approved.

## Dependencies and integration checkpoints

- No earlier Epic is required for Epic 1 refinement.
- Stories 1.1 and 1.2 share the auth/current-account contract and must coordinate
  through `E1-COORD-AUTH-001`.
- Story 1.3 establishes the Profile aggregate. Stories 1.4 through 1.7 may
  proceed in parallel only after `E1-COORD-PROFILE-001` is frozen.
- Story 1.8 depends on the complete supported Profile schema and owns the
  immutable snapshot boundary under `E1-COORD-VERSION-001`.
- Component and browser verification remain blocked by the separately owned
  `E1-COORD-TEST-001` enablement boundary.

## Shared boundaries

- Laravel/Vue User, session, CSRF, and current-account boundary.
- CV Profile aggregate, section schemas, ownership policy, write concurrency,
  API resource, and shared editor state.
- Immutable CV Version snapshot schema and downstream source identity.
- Project-level frontend component and end-to-end test tooling.
