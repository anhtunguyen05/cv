---
sprint_id: sprint-02-job-fit-refinement
title: Job Fit Epic Refinement
status: draft
start: null
end: null
facilitator: unassigned
goal: Produce a reviewed Epic 2 package and atomic task breakdowns for all five Job Fit stories without changing application code.
refinement_stories:
  - 2-1-save-a-job-description
  - 2-2-manage-saved-job-descriptions
  - 2-3-analyze-a-job-description
  - 2-4-generate-a-match-report
  - 2-5-review-an-explainable-match-report
committed_stories: []
capacity_assumptions:
  - This is a planning-only sprint; implementation capacity is not allocated here.
  - Team dates, facilitator, and available planning capacity are not yet confirmed.
constraints:
  - Planning artifacts only; no application, infrastructure, dependency, test, or runtime changes.
  - Epic 2 implementation depends on approved Epic 1 authentication and CV Version contracts.
  - Story lifecycle remains backlog until the package and blocking decisions are approved.
---

# Sprint 02: Job Fit Epic Refinement

## Goal and done signal

Create one coherent planning package for Epic 2 so a team can implement saved
Job Descriptions, deterministic analysis, Match Reports, and explainable report
review without inventing contracts or duplicating shared boundaries.

This refinement sprint is complete when all five Story packages pass BMAD
review; every material decision has an owner, resolution, and dated approval
evidence; cross-Epic and cross-Story checkpoints are accepted; and the sprint
integration owner explicitly synchronizes approved Story lifecycle through
`bmad-sprint-planning`.

Opening the planning PR completes the requested breakdown work. It does not
complete the sprint or authorize implementation.

## Readiness blockers

- Sprint dates, facilitator, and planning capacity are unassigned.
- `E1-COORD-AUTH-001` and `E1-COORD-VERSION-001` must expose approved consumer
  checkpoints before protected Job Fit work can start.
- `E2-DEC-001` through `E2-DEC-009` require owners and approval evidence.
- Shared frontend/E2E tooling requires the accepted cross-Epic test checkpoint.

## Dependency and sequencing summary

```text
Epic 1 auth ──> 2.1 Save JD ──> 2.2 Manage JD
                         └────> 2.3 Analyze JD ──┐
Epic 1 CV Version ──────────────────────────────┼──> 2.4 Match Report ──> 2.5 Review Report
                                               └── shared deterministic fixtures
```

- Story 2.2 consumes the logical Job Description and revision checkpoint from
  Story 2.1.
- Story 2.3 may begin after the same revision contract is frozen; its parser and
  persistence work can proceed independently of Story 2.2 mutation UI.
- Story 2.4 starts only after successful current-revision analysis and an
  approved Epic 1 immutable CV Version consumer contract.
- Story 2.5 consumes the immutable Match Report contract owned by Story 2.4.

## Coordination boundaries

- `E2-COORD-JD-001`: logical Job Description, revisions, ownership, and API.
- `E2-COORD-ANALYSIS-001`: deterministic analysis schema and rule version.
- `E2-COORD-MATCH-001`: scoring, evidence classification, and Match Report.
- `E2-COORD-TEST-001`: versioned fixtures, disposable MySQL, and browser tests.

## Scope control

No automatic document parsing, LLM analysis, AI rewriting, worker queue, ATS
ranking, or application code belongs in this sprint. Independent discoveries
must be recorded separately instead of expanding a current Story task.
