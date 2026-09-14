# Story 5.1 — Requirements

Shared [rules](../../business-rules.md), [contract](../../contracts.md),
[security](../../security-and-access.md), and [test strategy](../../test-strategy.md)
are authoritative.

<frozen-after-approval reason="human-owned behavior — do not modify unless human renegotiates">

**Always:** Build event fields from server-known allowlists; version redaction;
write append-only safe metadata for success/failure/cancel; authorize and audit
operator access; label records non-authoritative; bound query/export.

**Never:** Log raw CV/JD/Evidence/prompt/output, credentials, cookies, emails,
stack traces, or unbounded IDs/labels; accept client-authored audit truth; expose
an apply/write action; fall back to unsafe logs when redaction fails.

| Scenario | Expected behavior |
| --- | --- |
| Success/failure/timeout/cancel | Complete safe event with stable outcome/category/version |
| Nested secret/content in error or provider payload | Canary absent from every sink/export |
| Audit store unavailable | Approved fail mode plus safe operational signal; no raw fallback |
| Authorized bounded query | Paginated/filterable safe records and access audit |
| Unauthorized/foreign environment query | Non-disclosing denial |
| Duplicate/lost event write | Approved idempotent/reconciled append behavior |

</frozen-after-approval>

Backend owns event builder/redactor/store/query/RBAC; frontend, if approved,
owns safe read-only states/accessibility. Integration covers provider source,
retention and telemetry consumers. Verification uses content/secret canaries.
