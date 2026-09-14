---
story_key: 5-3-retain-and-delete-user-data-safely
title: Retain and delete User data safely
type: hardening
created: 2026-09-12
story_owner: unassigned
depends_on_stories: []
source_story: _bmad-output/planning-artifacts/epics.md
source_story_key: 5-3-retain-and-delete-user-data-safely
review_loop_iteration: 0
context:
  - _bmad-output/planning-artifacts/epics/epic-5-operational-safety/README.md
  - _bmad-output/planning-artifacts/epics/epic-5-operational-safety/contracts.md
  - _bmad-output/planning-artifacts/epics/epic-5-operational-safety/decisions.md
---

# Story 5.3: Retain and delete User data safely

**Lifecycle:** Read only from `_bmad-output/implementation-artifacts/sprint-status.yaml`.

**Delivery classification:** Post-MVP hardening.

**Planning blockers:** `E5-DEC-001`, `E5-DEC-002`, `E5-DEC-004`,
`E5-DEC-008`, `E5-COORD-RETENTION-001`, `E5-COORD-TEST-001`,
`DISCOVERY-E5-001`, and `DISCOVERY-E5-003` for production execution.

## Package map

| [Requirements](requirements.md) | [Contract](contract.md) | [Tasks](tasks.md) | [Verification](verification.md) |
| --- | --- | --- | --- |
| Policy/deletion/edge cases | Policy/request/status schema | Atomic work/dependencies | AC/evidence gate |

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

Apply an approved data-class retention policy and fulfill eligible User deletion
with scoped, isolated, consistent, recoverable processing and non-sensitive audit.

</frozen-after-approval>

## Canonical Acceptance Criteria

- **AC-5-3-retain-and-delete-user-data-safely-01:** When documented retention is
  reached, eligible messages/tool-call records/artifacts are handled by policy
  and retained trusted records stay internally consistent.
- **AC-5-3-retain-and-delete-user-data-safely-02:** When eligible User deletion
  completes, requested content and dependent non-authoritative records are
  deleted/anonymized by policy, unrelated Users remain unchanged, and the result
  is auditable without unnecessary sensitive content.

## Readiness coverage

Covers inventory/classification, legal authority/holds, clocks/actions/dependency
order, MySQL/storage/cache/backup/external systems, User/operator request/RBAC,
dry-run/approval/idempotency/batching/partial/rerun, concurrent writes, anonymization,
audit, isolation, recovery, accessibility, and E2E. Planning does not authorize production deletion.
