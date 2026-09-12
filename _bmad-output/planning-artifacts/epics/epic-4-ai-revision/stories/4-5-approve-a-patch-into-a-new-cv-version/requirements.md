# Story 4.5: Approve a Patch into a new CV Version — Requirements

Return to [Story overview](README.md); shared [rules](../../business-rules.md),
[data/lifecycle](../../data-and-lifecycle.md), [security](../../security-and-access.md),
and [UX/validation](../../ux-and-validation.md) remain authoritative.

<frozen-after-approval reason="human-owned behavior — do not modify unless human renegotiates">

**Always:** Require fresh explicit confirmation; derive ownership/source/status;
lock/recheck Patch and old value; validate target, Evidence, proposal and full
result snapshot; use one idempotent MySQL transaction; record source/Patch/new
Version provenance; return/reconcile the exact result.

**Never:** Accept a client-composed result snapshot, owner, status, or applied
Version ID; mutate source Version/Profile; apply rejected/invalid/applied/stale
Patch; split Version insert and Patch transition; let provider trigger approval;
report success before durable commit.

| Scenario | Expected behavior |
| --- | --- |
| Valid current pending Patch | One new immutable Version and applied Patch atomically |
| Old value/source/schema/Evidence mismatch | Stable stale/invalid conflict, no Version, no applied state |
| Duplicate click/lost success/retry | Same result Version under approved idempotency key |
| Concurrent approve versus reject/edit/approve | One transition winner; losers receive current safe state |
| Failure at Version/provenance/Patch write | Complete rollback and actionable safe retry |
| Foreign/missing nested source | Non-disclosing denial and no state change |

</frozen-after-approval>

## Concern coverage

Backend/domain own revalidation, pure transform, complete snapshot validation,
locks, transaction, Version/provenance creation, and Patch transition. Security
owns ownership graph, explicit human action, non-disclosure, and audit. Frontend
owns confirmation, pending/stale/success/failure/retry and exact result link.
Integration/verification use real MySQL, forced write failures, races, two Users,
and critical browser evidence.
