# Story 4.4: Review, edit, or reject a Patch — Requirements

Return to [Story overview](README.md); shared [rules](../../business-rules.md),
[security](../../security-and-access.md), and [UX/validation](../../ux-and-validation.md)
remain authoritative.

<frozen-after-approval reason="human-owned behavior — do not modify unless human renegotiates">

**Always:** Authorize exact Patch/source/Evidence graph; render safe attributable
diff; derive allowed actions from server status; keep edits inside target/schema;
revalidate edited proposal; confirm reject; use stale/idempotency control;
preserve proposal/source/decision history.

**Never:** Display provider text as current CV; broaden edit target; remove
Evidence provenance; let browser set status; edit/reject applied/invalid Patch;
overwrite source Version; disclose foreign Patch/source.

| Scenario | Expected behavior |
| --- | --- |
| Pending Patch | Safe source/proposal diff, reason, Evidence, provenance, actions |
| Empty/long/markup/unsupported proposal | Frozen safe/invalid presentation; no execution |
| Permitted valid edit | Attributable pending-validation proposal; source unchanged |
| Invalid/out-of-target edit | Field/domain error; current proposal/source unchanged |
| Confirmed reject | One immutable rejected decision; source unchanged |
| Repeated/concurrent/stale edit/reject/approve | One winner or idempotent same result; conflict recovery |
| Foreign/missing Patch | Non-disclosing state with no nested content |

</frozen-after-approval>

## Concern coverage

Backend owns status/allowed actions, Patch proposal revision, validation,
rejection, transaction, and concurrency. Security owns graph authorization,
safe provider/User text, audit, and non-disclosure. Frontend owns validated
adapter, semantic diff, edit form, confirmations, focus, conflict/reload/error.
Shared fixtures bind API fields/codes to UI states and verification.
