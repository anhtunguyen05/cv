# Story 1.4: Manage CV summary and skills — Requirements

Return to the [story overview](README.md). Shared Epic rules remain authoritative in [business rules](../../business-rules.md), [data and lifecycle](../../data-and-lifecycle.md), [security and access](../../security-and-access.md), and [UX and validation](../../ux-and-validation.md).

<frozen-after-approval reason="human-owned behavior — do not modify unless human renegotiates">

## Boundaries & Constraints

**Always:** Save structured fields atomically under authenticated ownership;
support approved empty categories; preserve unaffected fields and all Versions;
map nested field errors to their controls.

**Ask First:** Freeze summary and category schema, category/item limits,
duplicate/case/ordering policy, save granularity and conflict behavior.

**Never:** Treat skills as one opaque blob, mutate a Version, infer unsupported
skills, or interpret omitted fields as silent deletion.

## Story-specific rules and edge cases

- **SUMMARY-BR-001:** Empty summary is allowed only if the approved Profile
  schema says it is optional; whitespace-only and over-limit states are explicit.
- **SKILL-BR-001:** Each skill belongs to one approved category representation;
  empty/invalid entries are rejected rather than silently coerced.

| Scenario | Expected behavior |
| --- | --- |
| Valid summary/categories | Structured values save and reload |
| Optional empty category | Save succeeds under approved schema |
| Invalid/over-limit skill | Field error; previous aggregate remains unchanged |
| Stale or ambiguous update | Preserve edits and use approved reconciliation |
| Existing Version | Remains unchanged after Profile update |



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
