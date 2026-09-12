# Story 1.6: Manage projects — Requirements

Return to the [story overview](README.md). Shared Epic rules remain authoritative in [business rules](../../business-rules.md), [data and lifecycle](../../data-and-lifecycle.md), [security and access](../../security-and-access.md), and [UX and validation](../../ux-and-validation.md).

<frozen-after-approval reason="human-owned behavior — do not modify unless human renegotiates">

## Boundaries & Constraints

**Always:** Give projects stable server ULIDs; keep technology and bullet data
independently retrievable; validate nested structure/limits; enforce Profile
ownership; preserve siblings and Versions.

**Ask First:** Freeze project fields, role/URL/date semantics, technology and
bullet representation, ordering, limits, duplicates and save/removal behavior.

**Never:** Invent Evidence, collapse projects into skill strings, accept nested
IDs from another Profile, persist partial malformed projects, or mutate Versions.

## Story-specific rules and edge cases

- **PROJECT-BR-001:** A project requires an approved name and its technology
  and bullet collections retain their distinct meanings.
- **PROJECT-BR-002:** Editing/removing one stable project ID preserves every
  unrelated valid project and section.

| Scenario | Expected behavior |
| --- | --- |
| Valid project | Stable ID; role/technology/bullets reload independently |
| Invalid nested field/limit | Project-level field errors; no partial project |
| Edit/remove | Only targeted current Profile entry changes |
| Foreign/stale project ID | Non-disclosing/conflict outcome; no mutation |
| Existing Version | Original projects remain unchanged |



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
