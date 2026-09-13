# Story 3.3: Export a reviewed CV — Requirements

Return to the [Story overview](README.md). Shared rules remain authoritative in
[Epic business rules](../../business-rules.md), [data and lifecycle](../../data-and-lifecycle.md),
[security and access](../../security-and-access.md), and
[UX and validation](../../ux-and-validation.md).

<frozen-after-approval reason="human-owned behavior — do not modify unless human renegotiates">

## Boundaries and constraints

**Always:** Export only a ready authorized Preview tuple; revalidate exact source
identity as approved; prepare shared content and print styles; keep failures
actionable; preserve retry context; distinguish invocation from completion.

**Ask first:** Resolve planning blockers and record approval evidence before
implementation.

**Never:** Read current Profile drafts; switch Version/Template silently; expose
foreign source; require AI/worker/server PDF; claim save/print success without
proof; persist duplicate artifacts; print hidden controls or sensitive internals.

## Story-specific rules and edge cases

- **CV-EXPORT-BR-001:** Export is enabled only when the current exact Preview
  tuple is ready; a selection/source/version change invalidates readiness.
- **CV-EXPORT-BR-002:** Print preparation is idempotent and has explicit timeout,
  asset/font readiness, and cleanup behavior.
- **CV-EXPORT-BR-003:** Dialog cancel/return, repeated clicks, popup restrictions,
  unsupported browser, navigation, session expiry, and multi-tab source changes
  never create false success or unexplained duplicate state.

| Scenario | Expected behavior |
| --- | --- |
| Ready owned Preview | Same source/content enters approved print surface |
| Profile edited after Version | Export remains the saved snapshot |
| Source/Template changes during preparation | Abort stale intent and require explicit retry/review |
| Browser print blocked/unsupported or asset fails | Actionable safe error with source context retained |
| Dialog closes or is cancelled | Return to Preview without unverifiable success claim |
| Foreign/missing Version | Non-disclosing denial before content/artifact exposure |

</frozen-after-approval>

## Coverage by concern

| Concern | Authoritative coverage |
| --- | --- |
| Behavior | Rules/scenarios above and canonical ACs in [README](README.md) |
| Contract | Story slice in [contract.md](contract.md) plus Epic/global envelopes |
| Backend | Authorized immutable source; optional intent-only audit only if approved |
| Security | Ownership, non-disclosure, safe print content/assets, sensitive metadata |
| Validation | Source tuple/readiness, browser capability, preparation/asset/timeout/state races |
| Frontend | Export control, print surface/styles, preparing/error/return/retry states |
| Integration | Shared renderer tuple/content, print browser wrapper, fixture/baseline ownership |
| Verification | AC mapping and evidence gates in [verification.md](verification.md) |
