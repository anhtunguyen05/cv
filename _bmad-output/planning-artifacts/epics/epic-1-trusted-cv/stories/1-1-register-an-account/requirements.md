# Story 1.1: Register an account — Requirements

Return to the [story overview](README.md). Shared Epic rules remain authoritative in [business rules](../../business-rules.md), [data and lifecycle](../../data-and-lifecycle.md), [security and access](../../security-and-access.md), and [UX and validation](../../ux-and-validation.md).

<frozen-after-approval reason="human-owned behavior — do not modify unless human renegotiates">

## Boundaries & Constraints

**Always:** Canonicalize and validate approved fields; enforce email uniqueness
at application and database boundaries; hash through Laravel; create the User
and session under the approved failure model; prevent duplicate submission;
never return/log credentials.

**Ask First:** Resolve the five blocking Epic decisions and record approval
evidence before implementation.

**Never:** Add recovery, social login, roles, MFA, Profile creation, or a second
auth mechanism; expose an existing User; store browser bearer credentials.

## Story-specific rules and edge cases

- **REG-BR-001:** Registration accepts only `name`, `email`, `password`, and an
  approved confirmation field; a User/role/verification flag is not writable.
- **REG-BR-002:** Concurrent requests for one canonical email create at most one
  User and expose the same safe losing outcome as a normal duplicate.
- **REG-BR-003:** If persistence commits but the response is lost, the client
  reconciles current-account state before presenting a retry that could create
  or confuse identities.

| Scenario | Expected behavior |
| --- | --- |
| Valid guest submission | One User, regenerated session, `data.user`, no credential fields |
| Duplicate or concurrent duplicate | No second User; generic approved email error |
| Missing/invalid/oversized/malformed input | No User/session mutation; field or global safe error |
| Authenticated caller | No new User and no identity replacement |
| CSRF/session/server failure | Approved recoverable or terminal state; no retry loop or partial success |



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
