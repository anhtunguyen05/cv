# Epic 1 Security and Access

## Shared requirements

- **E1-SEC-001 — Stateful SPA auth:** Account flows use Sanctum stateful,
  HttpOnly cookie sessions with CSRF protection. The implementation must prove
  approved origins, credentialed CORS, cookie attributes, regeneration,
  invalidation, bootstrap, and expiry recovery.
- **E1-SEC-002 — Generic authentication failure:** Unknown email and incorrect
  password have the same status, code, message, response shape, and User-visible
  behavior. Logs do not reveal supplied credentials.
- **E1-SEC-003 — Registration privacy:** Duplicate registration returns only
  the approved generic field error and never an existing User representation.
  The product owner must explicitly accept or change the remaining existence
  signal.
- **E1-SEC-004 — Server authorization:** Every Profile/Version query or command
  starts from the authenticated User boundary or applies an equivalent policy;
  route-model binding alone is insufficient.
- **E1-SEC-005 — Non-disclosing ownership:** Cross-user access is externally
  indistinguishable from absence and cannot be inferred through payload,
  status, timing-sensitive secondary lookups, logs, or cache state.
- **E1-SEC-006 — Abuse protection:** Registration and sign-in have separately
  approved limiter keys, trusted-proxy handling, thresholds, windows, counted
  outcomes, store semantics, and `Retry-After` behavior.
- **E1-SEC-007 — Browser credential hygiene:** The web client never persists
  password, session cookie, CSRF token, or bearer credential in Pinia,
  localStorage, sessionStorage, analytics, or error reports.
- **E1-SEC-008 — Private caching:** Current account, Profile, and Version
  responses use private/no-store behavior appropriate to their sensitivity;
  logout/expiry clears or invalidates cached User-owned responses.

## Threat-oriented checks

- Concurrent duplicate registration creates at most one User.
- Session fixation is prevented on register/sign-in; logout invalidates the
  server session and browser-visible auth state.
- Authenticated callers cannot register a second identity or silently replace
  their acting identity.
- Nested item IDs from another User or Profile cannot be attached, edited, or
  removed through a bulk Profile payload.
- Version creation checks source Profile ownership inside the transaction and
  cannot snapshot a stale or cross-user source.
- Validation and exception logs redact credentials and CV content while
  retaining safe correlation metadata.

## Human approvals required

Security implementation is blocked until `E1-DEC-001`, `E1-DEC-002`,
`E1-DEC-007`, and `E1-DEC-009` are approved. Those decisions own topology,
account policy, endpoint/failure behavior, abuse limits, ambiguous-success
recovery, and authenticated-route behavior.
