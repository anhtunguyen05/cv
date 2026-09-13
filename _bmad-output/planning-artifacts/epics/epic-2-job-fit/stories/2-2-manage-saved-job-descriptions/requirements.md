# Story 2.2: Manage saved Job Descriptions — Requirements

Return to the [Story overview](README.md) and shared Epic [business rules](../../business-rules.md),
[data/lifecycle](../../data-and-lifecycle.md), [security](../../security-and-access.md),
and [UX/validation](../../ux-and-validation.md).

<frozen-after-approval reason="human-owned behavior — do not modify unless human renegotiates">

## Boundaries and constraints

**Always:** Authorize the logical resource; require the current concurrency
precondition; insert rather than mutate a revision; atomically advance the
current pointer; keep prior derived records; logically delete only the root;
expose deleted context safely to pinned historical reports.

**Ask first:** Resolve Story blockers and approve how list/history/deleted state
is represented before implementation.

**Never:** Update an existing revision; copy a prior Analysis onto a new
revision; hard-delete pinned history; restore a deleted resource implicitly;
allow new analysis/matching after deletion; derive owner from the payload.

## Story-specific rules and edge cases

- **JD-MANAGE-BR-001:** A no-op edit follows an explicit approved policy; it
  cannot silently create unbounded identical revisions by accidental retry.
- **JD-MANAGE-BR-002:** A stale or competing update creates no revision and
  returns current identity needed for safe User reconciliation without leaking
  content across owners.
- **JD-MANAGE-BR-003:** Delete is idempotent or returns a stable terminal result
  as approved; delete/update/analyze races resolve to one documented state.

| Scenario | Expected behavior |
| --- | --- |
| Valid update of current active JD | One new immutable revision becomes current; owner unchanged |
| Existing prior Analysis/report | Prior sources/output unchanged; new current revision is unanalyzed |
| Invalid or stale update | No revision/current-pointer mutation; User input preserved in UI |
| Logical delete | Removed from active list and new work; pinned history remains owner-readable |
| Concurrent update/delete/analyze | Approved winner/loser outcomes; no partial or orphan records |
| Foreign/missing/deleted direct identifier | Non-disclosing or approved deleted historical behavior |

</frozen-after-approval>

## Coverage by concern

| Concern | Authoritative coverage |
| --- | --- |
| Behavior | Revision/delete/history scenarios and canonical ACs |
| Contract | [contract.md](contract.md), shared JD/revision/error contracts |
| Backend | Revision transaction, logical deletion, historical resolver, list/query ownership |
| Security | Session, owner-only current/history, non-disclosure, safe logs |
| Validation | Updated fields, clearing semantics, concurrency token, deletion preconditions |
| Frontend | List/edit/delete confirmation, stale preservation, deleted banner, cache invalidation |
| Integration | Shared fixtures and coordination with Analysis/Match consumers |
| Verification | [verification.md](verification.md) and historical reproducibility gate |
