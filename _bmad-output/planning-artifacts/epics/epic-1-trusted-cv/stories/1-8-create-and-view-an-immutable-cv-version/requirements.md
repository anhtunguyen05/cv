# Story 1.8: Create and view an immutable CV Version — Requirements

Return to the [story overview](README.md). Shared Epic rules remain authoritative in [business rules](../../business-rules.md), [data and lifecycle](../../data-and-lifecycle.md), [security and access](../../security-and-access.md), and [UX and validation](../../ux-and-validation.md).

<frozen-after-approval reason="human-owned behavior — do not modify unless human renegotiates">

## Boundaries & Constraints

**Always:** Check source ownership and versionability inside the transaction;
capture the complete approved Profile schema with a schema version; use a new
ULID and UTC timestamp; order lists deterministically; read stored snapshots.

**Ask First:** Freeze name/duplicate policy, snapshot content/versioning,
transaction and stale-source behavior, pagination/sort, cache and first Version
route through the Epic decisions.

**Never:** Snapshot unsaved browser state, update/delete a Version, read live
Profile fields to render history, disclose foreign resources, or add downstream
matching/Preview/Export behavior.

## Story-specific rules and edge cases

- **VERSION-BR-001:** Snapshot creation is all-or-nothing and contains every
  supported section, including approved empty optional sections.
- **VERSION-BR-002:** Version detail is derived only from immutable Version
  state; the source Profile is a reference, not a live content dependency.
- **VERSION-BR-003:** A Version list is owner-scoped, newest first, with a
  stable tie-breaker and approved pagination metadata.

| Scenario | Expected behavior |
| --- | --- |
| Valid owned Profile/name | Complete immutable snapshot created and reloadable |
| Later Profile edit | Existing Version content remains original |
| Multiple Versions | Only owner's Versions, deterministic reverse creation order |
| Invalid name/source state | Field/conflict error; no Version |
| Foreign Profile/Version ID | Non-disclosing not found; no snapshot/disclosure |
| Concurrent snapshot/Profile edit | Approved consistent source or safe conflict, never mixed snapshot |



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
