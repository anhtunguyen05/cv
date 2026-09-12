---
story_key: 4-5-approve-a-patch-into-a-new-cv-version
title: Approve a Patch into a new CV Version
type: feature
created: 2026-09-12
story_owner: unassigned
depends_on_stories:
  - 1-8-create-and-view-an-immutable-cv-version
  - 4-4-review-edit-or-reject-a-patch
source_story: _bmad-output/planning-artifacts/epics.md
source_story_key: 4-5-approve-a-patch-into-a-new-cv-version
review_loop_iteration: 0
context:
  - _bmad-output/planning-artifacts/epics/epic-4-ai-revision/README.md
  - _bmad-output/planning-artifacts/epics/epic-4-ai-revision/contracts.md
  - _bmad-output/planning-artifacts/epics/epic-4-ai-revision/decisions.md
---

# Story 4.5: Approve a Patch into a new CV Version

**Lifecycle:** Read only from `_bmad-output/implementation-artifacts/sprint-status.yaml`.

**Planning blockers:** `E4-PREREQ-VERSION-001`, `E4-DEC-001`, `E4-DEC-004`,
`E4-DEC-007` through `E4-DEC-009`, `E4-COORD-PATCH-001`,
`E4-COORD-APPLY-001`, and `E4-COORD-TEST-001`.

## Package map

| File | Purpose |
| --- | --- |
| [Requirements](requirements.md) | Approval, stale/apply/domain, security, transaction, UX, integration, edge cases |
| [Contract](contract.md) | Approve request/response/status/error and responsibility boundaries |
| [Tasks](tasks.md) | Atomic tasks with dependency, scope, blocker, and verification metadata |
| [Verification](verification.md) | AC mapping, atomicity/race evidence, and exit gate |

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

Apply only an owned valid pending Patch after a fresh explicit User decision,
revalidate it against its exact immutable source, and atomically create one new
CV Version without overwriting history.

</frozen-after-approval>

## Canonical Acceptance Criteria

- **AC-4-5-approve-a-patch-into-a-new-cv-version-01:** Given I own a valid
  pending Patch whose source has not changed, when I explicitly approve it, then
  the system revalidates, creates one new Version with the change, marks the
  Patch applied, and leaves the source Version unchanged.
- **AC-4-5-approve-a-patch-into-a-new-cv-version-02:** Given the Patch old value
  no longer matches its source, when I approve, then approval is rejected as
  stale, no Version is created, and the Patch is not applied.
- **AC-4-5-approve-a-patch-into-a-new-cv-version-03:** Given persistence fails
  during approval, when failure returns, then source and Patch status remain
  consistent and I receive an actionable retry outcome.

## Readiness coverage

Covers explicit confirmation, complete ownership graph, Patch/status/schema/
Evidence/source/old-value revalidation, deterministic transform, full snapshot
validation, Version name/provenance, MySQL transaction/locks/idempotency,
competing approve/reject, lost response, rollback injection, FE/API states,
accessibility, and E2E.

## References

- Epic: `E4-BR-001` through `E4-BR-004`, `E4-BR-011` through `E4-BR-022`,
  `E4-CONTRACT-PATCH-001`, `E4-CONTRACT-APPLY-001`, `E4-CONTRACT-ERROR-001`,
  `E4-DATA-003`, `E4-SEC-001` through `E4-SEC-006`,
  `E4-COORD-PATCH-001`, `E4-COORD-APPLY-001`, `E4-COORD-TEST-001`.
- Code map: `apps/api/app/`, `database/migrations/`, `routes/api.php`,
  `apps/web/src/`; shared Version/Patch paths require coordination.
