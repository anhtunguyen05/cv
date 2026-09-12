---
sprint_id: sprint-04-ai-revision-refinement
title: Evidence-Based AI Revision Epic Refinement
status: draft
start: null
end: null
facilitator: unassigned
goal: Produce a reviewed Epic 4 package and atomic task breakdowns for all six Evidence and Patch stories without changing application code.
refinement_stories:
  - 4-1-start-an-evidence-interview
  - 4-2-answer-evidence-questions
  - 4-3-generate-a-patch-proposal
  - 4-4-review-edit-or-reject-a-patch
  - 4-5-approve-a-patch-into-a-new-cv-version
  - 4-6-regenerate-a-rejected-or-invalid-patch
committed_stories: []
capacity_assumptions:
  - This is a planning-only sprint; implementation capacity is not allocated here.
  - Epic 4 is post-MVP and no provider, privacy, evaluation, or operational owner is assigned.
constraints:
  - Planning artifacts only; no application, infrastructure, dependency, prompt, provider, worker, test, or runtime changes.
  - AI remains proposal-only; only Laravel may validate/persist state and explicit User approval may create a new immutable CV Version.
  - Story lifecycle remains backlog until the package, prerequisites, and blocking decisions are approved.
---

# Sprint 04: Evidence-Based AI Revision Epic Refinement

## Outcome

Review one coherent Epic 4 planning package that separates User Evidence,
provider output, validated Patch proposals, human decisions, and transactional
CV Version creation into assignable, non-overlapping work.

## Planning boundary

- Decompose all canonical Epic 4 acceptance criteria into atomic tasks.
- Record provider/privacy/evaluation decisions and cross-Story checkpoints.
- Keep prompt/tool schema, Evidence, Patch, apply transaction, and shared test
  boundaries under named coordination ownership.
- Do not implement or install application, provider, worker, prompt, test, or CI
  changes.

## Refinement sequence

1. Freeze Match Report and CV Version consumer prerequisites.
2. Freeze interview areas/questions and Evidence provenance/lifecycle.
3. Freeze provider request/result and Patch allowlist/validation.
4. Freeze review/edit/reject/regenerate state transitions.
5. Freeze atomic approval, stale checks, and new Version provenance.
6. Approve security, quality evaluation, failure, and E2E evidence.

## Exit evidence

- Six permanent Story folders with requirements, contract, tasks, and
  verification files.
- Every task carries lifecycle, ownership, branch, dependency, scope,
  coordination, blocker, acceptance, and evidence metadata.
- Canonical ACs map to task and verification layers.
- `bmad-review` findings are resolved or registered as human decisions.
- `bmad-sprint-planning` remains valid with Epic 4 Stories in `backlog`.
