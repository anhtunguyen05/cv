# Story 1.2: Sign in and sign out — Requirements

Return to the [story overview](README.md). Shared Epic rules remain authoritative in [business rules](../../business-rules.md), [data and lifecycle](../../data-and-lifecycle.md), [security and access](../../security-and-access.md), and [UX and validation](../../ux-and-validation.md).

<frozen-after-approval reason="human-owned behavior — do not modify unless human renegotiates">

## Boundaries & Constraints

**Always:** Use generic invalid-credential behavior; regenerate/invalidate the
server session; clear protected client state on logout/expiry; never expose or
persist credentials; keep User-owned resources unchanged.

**Ask First:** Freeze limiter, logout idempotency, safe return route, expiry
recovery and cache-clearing behavior through the Epic decisions.

**Never:** Add remember-me, recovery, social login, MFA, bearer storage, or a
parallel current-account/User shape.

## Story-specific rules and edge cases

- **LOGIN-BR-001:** Unknown email and wrong password are externally identical.
- **LOGOUT-BR-001:** A successful logout invalidates the active application
  session and client cache without editing User data.
- **AUTH-BR-001:** Session expiry removes protected content before redirect;
  stale in-flight responses cannot repopulate it.

| Scenario | Expected behavior |
| --- | --- |
| Valid credentials | Regenerated authenticated session and shared Public User |
| Unknown email/wrong password | Same generic error and no authenticated state |
| Current account | Private/no-store shared Public User or `UNAUTHENTICATED` |
| Sign out | Session invalidated, protected cache cleared, later access rejected |
| Expiry during protected work | Data hidden, safe sign-in state, no retry loop |



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
