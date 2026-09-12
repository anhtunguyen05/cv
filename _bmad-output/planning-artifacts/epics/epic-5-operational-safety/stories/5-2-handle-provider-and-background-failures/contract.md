# Story 5.2 — Contract Slice

Refines `E5-CONTRACT-JOB-001` and provider failure taxonomy.

| Operation | Success/state | Failure behavior |
| --- | --- | --- |
| Provider attempt | Validated result passed to application boundary | Stable retryable/terminal category, bounded attempts, no trusted partial result |
| Create/status/cancel/retry named job | Exact owner/source/contract/idempotency and documented state/allowed actions | Non-disclosing, conflict, terminal/retryable; stale worker cannot win |
| Commit job result | Atomic validated result reference plus `succeeded` | Rollback result/status; never status-success without result |

Async endpoints and persistence exist only after `E5-PREREQ-ASYNC-001` names an
operation. Freeze status/error, retry/cancel/lease/result, polling/push/cache,
audit/metric behavior in `E5-DEC-003`, `E5-DEC-005`.
