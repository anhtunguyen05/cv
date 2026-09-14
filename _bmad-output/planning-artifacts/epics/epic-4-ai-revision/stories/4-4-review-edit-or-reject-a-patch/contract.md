# Story 4.4: Review, edit, or reject a Patch — Contract Slice

Refines `E4-CONTRACT-PATCH-001`, `E4-CONTRACT-DECISION-001`, and
`E4-CONTRACT-ERROR-001`.

| Operation | Proposed request | Success | Failure |
| --- | --- | --- | --- |
| Read Patch | `GET /api/v1/patches/{patch}` | Safe exact source/proposal/Evidence/provenance/status/allowed actions | Non-disclosing missing/foreign, invalid schema |
| Edit proposal | `PATCH /api/v1/patches/{patch}`; allowed new value plus stale/idempotency precondition | Attributable proposal revision in approved pending-validation/pending flow | Validation, target/status/stale/conflict |
| Reject Patch | explicit decision endpoint plus confirmation intent and precondition | Idempotent rejected status/decision timestamp | Invalid status, concurrent decision, non-disclosing source |

API owns current status, target, validation, provenance, and transition. Frontend
renders validated data and never submits source/owner/status or approval. Freeze
proposal-revision semantics, fields, confirmations, allowed actions, status/error
matrix, concurrency, and audit through `E4-DEC-004`, `E4-DEC-007`.
