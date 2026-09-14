# Story 5.1 — Contract Slice

Refines `E5-CONTRACT-AUDIT-001`.

| Operation | Input | Success | Failure |
| --- | --- | --- | --- |
| Append event | Server-owned operation context and allowlisted outcome | Safe immutable event ID/metadata | Approved fail-open/closed signal; no unsafe fallback |
| Query events | Authorized operator, bounded filter/cursor | Paginated safe summaries/details marked operational | Denied, validation, unavailable; no content leak |
| Export evidence, if approved | Narrow filter, purpose, approval and limits | Private time-bound sanitized artifact plus access audit | Denied/oversize/failure with no partial public artifact |

No endpoint accepts an audit event as product input or exposes a CV/Patch write.
Freeze fields, codes, RBAC, redaction, store, retention, pagination and export in
`E5-DEC-001`, `E5-DEC-002`, `E5-DEC-004` before readiness.
