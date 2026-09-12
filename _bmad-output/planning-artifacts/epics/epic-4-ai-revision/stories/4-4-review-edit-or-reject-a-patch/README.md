---
story_key: 4-4-review-edit-or-reject-a-patch
title: Review, edit, or reject a Patch
type: feature
created: 2026-09-12
story_owner: unassigned
depends_on_stories:
  - 4-3-generate-a-patch-proposal
source_story: _bmad-output/planning-artifacts/epics.md
source_story_key: 4-4-review-edit-or-reject-a-patch
review_loop_iteration: 0
context:
  - _bmad-output/planning-artifacts/epics/epic-4-ai-revision/README.md
  - _bmad-output/planning-artifacts/epics/epic-4-ai-revision/contracts.md
  - _bmad-output/planning-artifacts/epics/epic-4-ai-revision/decisions.md
---

# Story 4.4: Review, edit, or reject a Patch

**Lifecycle:** Read only from `_bmad-output/implementation-artifacts/sprint-status.yaml`.

**Planning blockers:** `E4-DEC-001`, `E4-DEC-004`, `E4-DEC-007` through
`E4-DEC-009`, `E4-COORD-PATCH-001`, and `E4-COORD-TEST-001`.

## Package map

| File | Purpose |
| --- | --- |
| [Requirements](requirements.md) | Review/diff/edit/reject rules, security, validation, UX, integration, edge cases |
| [Contract](contract.md) | Read/edit/reject request, response, status/error and responsibility boundaries |
| [Tasks](tasks.md) | Atomic tasks with dependency, scope, blocker, and verification metadata |
| [Verification](verification.md) | AC mapping and evidence gate |

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

Make the proposal, source value, rationale, and supporting User Evidence clear;
allow only bounded attributable edits or explicit rejection while the source CV
Version remains immutable.

</frozen-after-approval>

## Canonical Acceptance Criteria

- **AC-4-4-review-edit-or-reject-a-patch-01:** Given I own a pending Patch, when
  opened, then I see old/new values, reason, Evidence sources, and can distinguish
  proposal from current CV Version.
- **AC-4-4-review-edit-or-reject-a-patch-02:** Given I edit a permitted proposed
  value, when saved, then the edited proposal remains pending validation and the
  current CV Version is unchanged.
- **AC-4-4-review-edit-or-reject-a-patch-03:** Given I reject a pending Patch,
  when confirmed, then status becomes rejected and source Version is unchanged.

## Readiness coverage

Covers immutable source/proposal diff, provenance, allowed actions, edit
allowlist/revalidation, rejection confirmation/history, stale/repeated/concurrent
decisions, ownership, unsafe/long content, accessibility, FE/API fixtures,
MySQL state constraints, and E2E.

## References

- Epic: `E4-BR-001` through `E4-BR-018`, `E4-CONTRACT-PATCH-001`,
  `E4-CONTRACT-DECISION-001`, `E4-CONTRACT-ERROR-001`, `E4-DATA-002`,
  `E4-SEC-001` through `E4-SEC-006`, `E4-COORD-PATCH-001`, `E4-COORD-TEST-001`.
- Code map: `apps/api/app/`, `routes/api.php`, `apps/web/src/`; shared Patch
  schema/diff/fixture paths require the coordination checkpoint.
