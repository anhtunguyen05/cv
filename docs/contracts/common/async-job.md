# Async Job and Result Contract

Async work is post-MVP unless a story proves it is required. A worker never
mutates trusted state directly.

- **ASYNC-CONTRACT-001:** Laravel creates a job with a ULID, owner reference,
  immutable input/source snapshot references, operation type, and lifecycle
  state (`queued`, `running`, `succeeded`, `retryable_failed`, `failed`,
  `cancelled`).
- **ASYNC-CONTRACT-002:** The worker receives only a scoped job payload and
  returns a result to a Laravel-owned authenticated boundary. The result includes
  job ULID, input snapshot references, outcome, and idempotency key.
- **ASYNC-CONTRACT-003:** Laravel validates ownership, idempotency, input
  snapshot freshness, and lifecycle transition before accepting a result. Stale,
  duplicate, or unauthorized results cannot create or change trusted state.
- **ASYNC-CONTRACT-004:** Retry, cancellation, retention, and dead-letter
  settings are capability-specific decisions; they must be explicit before an
  async operation is enabled.
