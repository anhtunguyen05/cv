# Story 1.3: Create a CV Profile — Requirements

Return to the [story overview](README.md). Shared Epic rules remain authoritative in [business rules](../../business-rules.md), [data and lifecycle](../../data-and-lifecycle.md), [security and access](../../security-and-access.md), and [UX and validation](../../ux-and-validation.md).

<frozen-after-approval reason="human-owned behavior — do not modify unless human renegotiates">

## Boundaries & Constraints

**Always:** Derive ownership from the session; validate the whole requested
aggregate; use a server ULID; persist atomically; return the shared Profile
shape; preserve valid form values on failure.

**Ask First:** Freeze Profile/personal fields, limits, endpoint/write shape,
persistence split, concurrency token, first Profile route and test tooling.

**Never:** Accept owner IDs, create partial Profiles, add Profile deletion,
create a Version, or disclose cross-user existence.

## Story-specific rules and edge cases

- **PROFILE-CREATE-BR-001:** A create request yields one Profile or no Profile.
- **PROFILE-CREATE-BR-002:** A newly created Profile initializes unsupported or
  optional sections only according to the shared schema, not ad-hoc defaults.

| Scenario | Expected behavior |
| --- | --- |
| Valid title/personal data | Owned Profile created and reloadable |
| Empty/over-limit title or invalid field | Field errors; no partial Profile |
| Duplicate submit/lost response | Reconcile approved create outcome before retry |
| Cross-user ID access | Non-disclosing not found; no mutation |
| Expired auth | Protected data cleared and create rejected |



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
