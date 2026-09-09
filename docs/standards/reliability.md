# Reliability Standard

References: AD-4, AD-8, AD-17.

- **REL-STD-001:** A failed analysis, Match Report, Preview, Export, or future provider call may
  not leave partial trusted state presented as successful.
- **REL-STD-002:** Each operation has an explicit success, retryable failure, or terminal failure
  state exposed through the HTTP contract and usable web UI state.
- **REL-STD-003:** Derived output records the exact source IDs and rule/template versions used;
  re-running a deterministic operation must not silently change historic output.
- **REL-STD-004:** Keep synchronous MVP work synchronous unless measured latency or reliability
  need justifies a queue. A future worker consumes explicit job/result contracts
  and cannot mutate trusted state directly.
- **REL-STD-005:** Retry only idempotent or explicitly deduplicated work. Async lifecycle,
  retry budgets, and dead-letter policy are deferred until an async capability is
  approved.
