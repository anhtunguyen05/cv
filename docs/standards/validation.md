# Validation Standard

References: AD-19 and [HTTP contract](../contracts/common/http.md).

- **VAL-STD-001:** Laravel Form Requests enforce HTTP shape, types, required fields, limits, and
  request-context authorization checks.
- **VAL-STD-002:** Application/domain code enforces business invariants, ownership, immutable
  state, transition preconditions, and concurrency/source freshness for every
  caller, including queues and future workers.
- **VAL-STD-003:** Zod/VeeValidate improves immediate web UX only. A browser schema does not
  authorize a write or redefine a backend rule.
- **VAL-STD-004:** Contract changes update the contract document, Form Request, domain rule,
  frontend schema, and corresponding tests in the same story.
- **VAL-STD-005:** Validation failures use `VALIDATION_FAILED` and field-keyed `details`.
