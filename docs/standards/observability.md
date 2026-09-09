# Observability and Audit Standard

References: AD-18.

## Application logs

Emit structured operational events with timestamp, severity, service, operation,
outcome, and a correlation/request identifier when observability infrastructure
is introduced. Do not log sensitive user content, cookies, credentials, raw AI
payloads, or full authorization headers.

## Audit records

**OBS-STD-001:** Audit is business evidence, not a debug log. A record is
append-only and contains actor type plus the existing User bigint identity
serialized as a string, action, target resource ULID, outcome, timestamp, and
sanitized metadata. It does not contain a raw CV, raw Job Description, raw
prompt, provider secret, or session cookie.

**OBS-STD-002:** Post-MVP AI records may include provider/model, prompt-template version,
timing, status, redacted metadata, and references to protected records. Raw
payload retention and deletion periods are deferred to Epic 5.
