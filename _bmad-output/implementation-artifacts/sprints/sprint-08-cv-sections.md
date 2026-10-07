---
sprint_id: sprint-08-cv-sections
title: CV Sections
status: draft
start: null
end: null
facilitator: unassigned
goal: Deliver and independently verify the four structured CV section journeys while preserving Profile concurrency and Version regression guarantees.
refinement_stories:
  - 1-4-manage-cv-summary-and-skills
  - 1-5-manage-education-and-experience
  - 1-6-manage-projects
  - 1-7-manage-supplementary-cv-sections
committed_stories: []
capacity_assumptions:
  - One Profile integration owner serializes shared aggregate/editor changes; independent section owners may work in parallel only after that baseline is accepted.
constraints:
  - The charter remains draft until facilitator, dates, and capacity are confirmed; no Story lifecycle changes are implied.
  - Section work must use complete section replacement and `If-Match` from Profile v1; it must not introduce local Profile routes or schemas.
---

# Sprint 08: CV Sections

## Outcome and done signal

Deliver summary/skills, education/experience, projects, and supplementary
sections as independently testable modules over the approved Profile v1
aggregate, including stale-write behavior. The Epic-level Version regression
gate runs after Sprint 09 exposes its shared snapshot fixture.

The measurable done signal is passing persistence/API/component/browser evidence
for every section AC, preserved collection identity/order, controlled stale
conflicts, and an Epic integration gate scheduled against Sprint 09's Version
fixture before Epic 1 closes.

## Capacity and dates

Dates remain unset. Parallelism is earned only after the shared Profile baseline
is stable; capacity must reserve integration and regression-test time.

## Entry gates

- Profile v1 and its governing decisions are approved.
- Sprint 07 must provide the Profile persistence/fixture baseline, and Sprint
  06 must provide the disposable verification harness.
- Each implementation assignment must reserve the shared files through
  `E1-COORD-PROFILE-001`; section modules that do not overlap may proceed in
  parallel after that reservation is satisfied.

## Dependencies and sequence

Depends on Sprint 07's Profile persistence checkpoint and Sprint 06's test
harness. Delivery phases are: (1) establish the shared aggregate/editor/fixture
boundary; (2) run section modules whose reserved scopes do not overlap in
parallel; (3) run shared stale-write and collection regressions; (4) after
Sprint 09 establishes its fixture, run the Epic-level immutable-Version
regression gate.

## Delivery evidence gates

1. Every section mutation sends the current ETag; a `409` preserves draft
   input, reloads the authoritative Profile, and requires user-mediated
   reconciliation. The shared suite includes edits to different sections from
   stale tabs.
2. Before parallel work begins, the integration owner records a reservation
   matrix for the shared API resource, Profile document schema, routes, fixture
   catalog, frontend query/cache state, and cross-section regression suite.
   Any assignment touching one of those boundaries is serialized.
3. Each section proves atomic rejection/no mutation for invalid nested input,
   explicit removal only, server item-ID generation, request-order preservation
   after reload, and unchanged sibling sections after replacement.

All membership is recorded in this charter frontmatter. No Story lifecycle or task lifecycle is copied here. An external issue tracker may mirror permanent task keys under the mapping and verification rules in this directory README.
