# Story 1.5: Manage education and experience — Requirements

Return to the [story overview](README.md). Shared Epic rules remain authoritative in [business rules](../../business-rules.md), [data and lifecycle](../../data-and-lifecycle.md), [security and access](../../security-and-access.md), and [UX and validation](../../ux-and-validation.md).

<frozen-after-approval reason="human-owned behavior — do not modify unless human renegotiates">

## Boundaries & Constraints

**Always:** Use stable server item ULIDs; validate dates/types/limits; scope item
mutation through the owned Profile; preserve unrelated entries and Versions.

**Ask First:** Freeze item fields, date/open-ended semantics, ordering,
collection limits, add/edit/remove payload and stale conflict behavior.

**Never:** Address entries by array index, attach another Profile's item,
persist malformed partial entries, or mutate a saved Version.

## Story-specific rules and edge cases

- **HISTORY-BR-001:** Education and experience remain distinguishable section
  types even when they share reusable date/location fields.
- **HISTORY-BR-002:** Removal targets one stable item ID and does not imply
  deletion of omitted sibling items.

| Scenario | Expected behavior |
| --- | --- |
| Valid add/edit | Stable item ID and reloadable structured values |
| Invalid fields/date range | Item-level errors; no malformed persistence |
| Remove one item | Only target leaves current Profile |
| Foreign/stale item ID | Non-disclosing or conflict outcome; no change |
| Existing Version | Original history remains unchanged |



</frozen-after-approval>

## Coverage by concern

| Concern | Authoritative coverage |
| --- | --- |
| Behavior | Story rules and scenario table above; canonical ACs in [README](README.md) |
| Contract | Story slice in [contract.md](contract.md), Epic shared contracts, and global HTTP/error standards |
| Backend | Domain/persistence rules above plus bounded backend tasks in [tasks.md](tasks.md) |
| Security | Story constraints plus Epic security/access rules and global security standards |
| Validation | Story edge cases plus Epic UX/validation rules and global validation standards |
| Frontend | User-visible states above plus frontend tasks and shared editor/auth boundaries |
| Integration | Story contract slice, coordination checkpoint IDs, and FE/API task dependencies |
| Verification | AC-to-task traceability and evidence gates in [verification.md](verification.md) |
