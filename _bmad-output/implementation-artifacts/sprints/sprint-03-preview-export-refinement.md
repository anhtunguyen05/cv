---
sprint_id: sprint-03-preview-export-refinement
title: Preview and Export Epic Refinement
status: draft
start: null
end: null
facilitator: unassigned
goal: Produce a reviewed Epic 3 package and atomic task breakdowns for all three Preview and Export stories without changing application code.
refinement_stories:
  - 3-1-select-a-template
  - 3-2-preview-a-saved-cv-version
  - 3-3-export-a-reviewed-cv
committed_stories: []
capacity_assumptions:
  - This is a planning-only sprint; implementation capacity is not allocated here.
  - Team dates, facilitator, and available planning capacity are not yet confirmed.
constraints:
  - Planning artifacts only; no application, infrastructure, dependency, test, or runtime changes.
  - Epic 3 implementation depends on an approved Epic 1 immutable CV Version reader contract.
  - Browser print/HTML is the MVP export path; server PDF and worker work require separate approval.
  - Story lifecycle remains backlog until the package and blocking decisions are approved.
---

# Sprint 03: Preview and Export Epic Refinement

## Outcome

Review one coherent Epic 3 planning package whose Stories can later be assigned
without redefining Template identity, snapshot rendering, print behavior, or
verification ownership.

## Planning boundary

- Decompose all canonical Epic 3 acceptance criteria into atomic tasks.
- Record human decisions and cross-Story checkpoints that block readiness.
- Keep Template, renderer, print stylesheet, and shared fixture changes under
  named coordination records.
- Do not implement routes, renderers, styles, browser automation, PDF services,
  dependencies, or CI configuration.

## Refinement sequence

1. Freeze Template catalog/version/availability semantics.
2. Freeze the saved CV Version render projection and section registry.
3. Freeze browser print/HTML acceptance criteria and retry behavior.
4. Approve accessibility, visual fixture, browser, and ownership evidence.
5. Publish/synchronize individual Stories only after their blockers close.

## Exit evidence

- Three permanent Story folders with requirements, contract, tasks, and
  verification files.
- Every task has status, owner, branch/worktree, dependencies, scope,
  coordination, blockers, acceptance, and verification metadata.
- Canonical ACs map to task and evidence layers.
- `bmad-review` findings are resolved or explicitly registered as decisions.
- `bmad-sprint-planning` validates with all Epic 3 lifecycle entries unchanged
  in `backlog`.
