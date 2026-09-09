# Security and Privacy Standard

References: AD-2, AD-13, AD-18.

## Target implementation status

Sanctum is an adopted target standard, not a currently installed dependency or
configured runtime behavior. The account-access story must install/configure it
and verify this contract before marking authentication complete.

- **SEC-STD-001:** The first-party Vue SPA uses Laravel Sanctum stateful HttpOnly
  cookie sessions with CSRF protection. Do not store JWTs or bearer tokens in
  browser storage.
- **SEC-STD-002:** Before enabling SPA auth, declare stateful frontend origins,
  credentialed CORS, session cookie domain/path/`SameSite`/`Secure` attributes,
  CSRF-cookie bootstrap, logout invalidation, and client handling for `401` and
  CSRF/session-expiry failure. The client sends credentials only to the approved
  API origin.
- **SEC-STD-003:** `auth:sanctum` and server-side ownership policies protect
  every User-owned resource. Identifier opacity never replaces authorization.
- **SEC-STD-004:** Treat CV data, raw Job Description text, credentials, cookies,
  and raw AI prompts/outputs as sensitive.
- **SEC-STD-005:** Sensitive values never enter ordinary logs, client errors,
  analytics, or raw exception responses. User content and generated artifacts
  are private by default.
- **SEC-STD-006:** Secrets live only in environment-managed configuration; never
  commit them, return them from an endpoint, or include them in audit records.
- **SEC-STD-007:** AI/provider/worker integrations return validated data through
  an application contract and never receive direct persistence authority.
