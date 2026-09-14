# Story 5.3 — Requirements

Shared [rules](../../business-rules.md), [contracts](../../contracts.md),
[data/lifecycle](../../data-and-lifecycle.md), and [security](../../security-and-access.md)
remain authoritative.

<frozen-after-approval reason="human-owned behavior — do not modify unless human renegotiates">

**Always:** Inventory every store/processor; apply approved authority/policy
version; derive scope server-side; dry-run; require approvals; checkpoint and
isolate batches; preserve referential invariants/holds; audit safe counts/outcome;
support idempotent retry and verification.

**Never:** Infer legal period; delete production from test/reset path; accept
arbitrary client IDs/table names; cross User/environment; erase required trusted
history without policy; retain deleted content in audit; claim completion with
partial/external/backup work pending.

| Scenario | Expected behavior |
| --- | --- |
| Retention cutoff reached | Eligible classes handled in dependency order; retained truth consistent |
| User request with eligible scope | Approved deletion/anonymization and safe terminal evidence |
| Hold/legal/required record | Explicit retained exception without unnecessary content exposure |
| Concurrent writes/requests or repeated run | Frozen boundary/idempotent result; no resurrected/duplicate work |
| Batch/store/external failure | Partial-retryable checkpoint, no false completion, safe resume |
| Two Users/shared references/backups/caches | Unrelated data unchanged; approved propagation/expiry proven |

</frozen-after-approval>

Backend/ops own policy evaluator, inventory, executor, locks/checkpoints, storage/
external adapters, scheduler and status. UI owns request/confirm/progress/outcome.
Verification uses disposable environments only and captures pre/post integrity.
