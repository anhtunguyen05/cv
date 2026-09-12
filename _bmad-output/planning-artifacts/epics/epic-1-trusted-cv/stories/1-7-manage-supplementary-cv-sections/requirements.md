# Story 1.7: Manage supplementary CV sections — Requirements

Return to the [story overview](README.md). Shared Epic rules remain authoritative in [business rules](../../business-rules.md), [data and lifecycle](../../data-and-lifecycle.md), [security and access](../../security-and-access.md), and [UX and validation](../../ux-and-validation.md).

<frozen-after-approval reason="human-owned behavior — do not modify unless human renegotiates">

## Boundaries & Constraints

**Always:** Keep all three sections optional; use stable item IDs; reject
invalid entries without deleting valid siblings; preserve ownership, aggregate
atomicity and Versions.

**Ask First:** Freeze each section's fields, limits, ordering, duplicates,
language proficiency vocabulary, certificate/date/URL semantics and removal.

**Never:** Make optional sections prerequisites for Version/Preview, coerce an
invalid entry into an empty one, attach foreign item IDs, or mutate Versions.

## Story-specific rules and edge cases

- **SUPP-BR-001:** Certificates, languages, and activities remain distinct
  section types and may all be empty.
- **SUPP-BR-002:** An invalid submitted entry cannot silently delete or replace
  previously valid entries.

| Scenario | Expected behavior |
| --- | --- |
| Valid entries | Stored/reloaded in corresponding section with stable IDs |
| One invalid entry | Field error; existing valid entries remain unchanged |
| All sections empty | Profile stays valid and versionable |
| Foreign/stale item ID | Non-disclosing/conflict outcome; no mutation |
| Existing Version | Original supplementary data remains unchanged |



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
