# Epic 3 Security and Access

## E3-SEC-001 — Ownership and non-disclosure

- Every Preview source request requires the approved session and server-side CV
  Version ownership check.
- Foreign and missing identifiers share the approved response and telemetry
  shape; catalog access never reveals another User's resources.
- Client route guards are UX only and never replace API authorization.

## E3-SEC-002 — Safe rendering

- CV content, Template metadata, labels, URLs, and filenames are untrusted.
- Render text through framework escaping; any rich formatting uses a reviewed
  allowlist and safe URL scheme policy.
- Templates are application-owned code/data, never arbitrary User HTML, CSS,
  component names, or remote resources.

## E3-SEC-003 — Browser and print boundary

- Print views must not expose session tokens, hidden controls, debug values,
  internal IDs beyond approved traceability, or data from another open source.
- External images/fonts/assets follow the approved privacy, CSP, availability,
  and print-repeatability policy.
- Browser print is a local User action; server logs do not claim a file was
  generated unless a future verifiable artifact contract exists.

## E3-SEC-004 — Sensitive data handling

- CV content is sensitive and excluded from routine logs, analytics payloads,
  crash messages, URLs, and telemetry.
- Cache/storage behavior is explicit for shared devices and session expiry.
- Screenshots and visual-test fixtures use synthetic data only.

## E3-SEC-005 — Abuse and resilience

- Apply bounded source size, request rate, render complexity, and repeated print
  action safeguards without corrupting selection state.
- Unsafe schema, asset failure, and renderer exceptions fail closed with safe
  errors and no partial trusted output.
