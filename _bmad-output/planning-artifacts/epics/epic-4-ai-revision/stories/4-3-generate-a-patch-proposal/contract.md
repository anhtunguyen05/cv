# Story 4.3: Generate a Patch proposal — Contract Slice

Refines `E4-CONTRACT-PROVIDER-001`, `E4-CONTRACT-PATCH-001`, and
`E4-CONTRACT-ERROR-001`.

| Operation | Proposed input | Success | Failure |
| --- | --- | --- | --- |
| Request Patch generation | Owned `interview_id`; approved idempotency/precondition, no provider/output fields | Accepted sync result or explicit job contract returns one validated pending Patch and versions | Ineligible/stale, throttle, retryable provider, terminal invalid, conflict |
| Provider invocation | Server-built minimum source/Evidence DTO plus allowlist and versions | Untrusted structured candidate plus correlation metadata | Timeout/cancel/rate/malformed/transport; no trusted state |
| Read Patch | Owned Patch ID | Exact source, target, diff, reason, Evidence, provenance, status/allowed actions | Non-disclosing missing/foreign, invalid schema |

Laravel alone validates and persists. Provider returns no trusted IDs/status and
cannot call persistence. Frontend never submits output or marks pending. Freeze
sync/async, endpoint/status, schema, versions, dedupe, failure/audit, and quality
matrices through `E4-DEC-001`, `E4-DEC-004`–`E4-DEC-006`, `E4-DEC-008`.
