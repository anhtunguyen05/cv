# Authentication lifecycle contract

Version: `auth-lifecycle-v2` (revision of `registration-v1`)

Owner: Epic 1, Stories 1.1 and 1.2 (`E1-COORD-AUTH-001`)

This is the approved stable contract for first-party browser registration,
authentication, current-account retrieval, sign-out, and session recovery. The
executable examples live in
[`fixtures/registration-v1.json`](fixtures/registration-v1.json). Backend and
frontend verification must consume that corpus instead of defining local
envelopes, User projections, or authentication behavior.

The legacy filename is retained as the reserved cross-story compatibility path;
its metadata identifies the `auth-lifecycle-v2` revision.

## Authentication boundary

- The Vue SPA uses Laravel Sanctum stateful sessions with HttpOnly cookies.
- Browser requests are same-origin in production. Local Vite proxies `/api/v1`
  and `/sanctum` to Laravel, so the browser does not call a second API origin.
- The client first requests `/sanctum/csrf-cookie`, then sends credentialed
  requests. It never stores a bearer token, session cookie, CSRF value,
  password, or password hash in Pinia, localStorage, sessionStorage, analytics,
  or error reports.
- The CSRF bootstrap establishes the browser `XSRF-TOKEN` cookie. State-changing
  requests send its decoded synthetic value in `X-CSRF-TOKEN`; fixtures never
  contain a real token.
- Registration and login regenerate the session. Logout invalidates the
  session and regenerates the CSRF token. A repeated logout with a valid CSRF
  boundary is idempotent and returns `204`.

## Public User projection

```json
{
  "id": "123",
  "name": "Nguyen Anh Tu",
  "email": "nguyen.anhtu@example.test"
}
```

`id` is the existing Laravel bigint serialized as a decimal string. The
projection must never contain `password`, `password_hash`, `remember_token`,
session cookies, CSRF material, bearer tokens, email-verification internals, or
other security metadata.

## Operations

| Operation | Request | Success | Failure and client action |
| --- | --- | --- | --- |
| CSRF bootstrap | `GET /sanctum/csrf-cookie` | Cookie/state established | `419 SESSION_EXPIRED` or safe bootstrap failure; refresh once, then show a recoverable error |
| Register | `POST /api/v1/auth/register` with `name`, `email`, `password`, `password_confirmation` only | `201`, `{ "data": { "user": PublicUser } }`; `Cache-Control: private, no-store` | `400 INVALID_REQUEST_BODY`, `422 VALIDATION_FAILED`, `409 AUTHENTICATED_REGISTRATION_FORBIDDEN`, `419 SESSION_EXPIRED`, `429 THROTTLED`, or `500 INTERNAL_ERROR` according to the fixture row |
| Sign in | `POST /api/v1/auth/login` with `email` and `password` only | `200`, `{ "data": { "user": PublicUser } }`; `Cache-Control: private, no-store`; regenerated session | `401 INVALID_CREDENTIALS` for unknown email or wrong password with identical public shape, `419 SESSION_EXPIRED`, `429 THROTTLED`, or `500 INTERNAL_ERROR` according to the fixture row |
| Current account | `GET /api/v1/auth/me` with the browser session; no User ID payload | `200`, `{ "data": { "user": PublicUser } }`; `Cache-Control: private, no-store` | `401 UNAUTHENTICATED`; clear protected state and redirect to the safe login return path |
| Sign out | `POST /api/v1/auth/logout` with the approved browser session and CSRF boundary | `204`; session invalidated and CSRF token regenerated | A repeated or guest logout with valid CSRF is also `204`; invalid CSRF is `419 SESSION_EXPIRED` |

All `/api/v1` failures use the common JSON error envelope from
`docs/contracts/common/http.md`; messages are safe for display and `details`
is field-keyed for `VALIDATION_FAILED`.

## Registration rules

- Name is Unicode-trimmed, preserves internal whitespace, and is at most 120
  characters.
- Email is trimmed and lowercased for the canonical lookup/storage key.
- Password is 12–72 characters. No forced upper/lowercase/number/symbol rule is
  imposed. Confirmation is required and must match.
- Email verification is deferred for this story.
- A duplicate or concurrent duplicate email returns the same generic
  `422 VALIDATION_FAILED` email-field outcome and never returns an existing
  User.
- An authenticated caller receives `409 AUTHENTICATED_REGISTRATION_FORBIDDEN`
  and no User/session identity mutation occurs.
- Registration throttling uses Redis-backed, separately hashed IP and
  canonical-email keys: 5 attempts per 10 minutes per IP and 3 attempts per
  10 minutes per email. Rejected and successful attempts count. `429` includes
  `Retry-After`.
- Sign-in throttling uses Redis-backed, separately hashed trusted-IP and
  canonical-email keys: 5 attempts per 10 minutes per IP and 3 attempts per 10
  minutes per email. Rejected and successful attempts count. `429` includes
  `Retry-After` and does not disclose account existence.

## Sign-in and sign-out rules

- Unknown email and wrong password are externally identical: same status, code,
  message, response shape, and no User disclosure.
- Login and logout accept no client-supplied User ID, bearer token, or remember
  flag. Browser ownership comes only from the Sanctum session.
- Successful logout changes session access only. It never edits or deletes User,
  Profile, Version, or other User-owned data.
- A protected `401` or `419` clears protected Vue Query data and auth state
  before redirecting to `/login?return_to=<internal-path>`. External return URLs
  are rejected, and stale in-flight responses cannot repopulate cleared state.

## Recovery and privacy

- A lost registration response is ambiguous. The client calls
  `GET /api/v1/auth/me` before offering one explicit retry. A `200` is success;
  a `401` permits the explicit retry; there is no automatic retry loop.
- Protected-state `401` or `419` clears protected Vue Query data and auth state,
  then redirects to `/login?return_to=<internal-path>`. External return URLs
  are rejected.
- Registration, sign-in, and current-account responses are private and
  `no-store`.
- The fixture corpus is synthetic and contains no real credentials, hashes,
  cookies, CSRF values, bearer tokens, or user data.

## References

- `E1-DEC-001`, `E1-DEC-002`, `E1-DEC-007`, `E1-DEC-009`
- `E1-CONTRACT-USER-001`, `E1-CONTRACT-AUTH-001`, `E1-CONTRACT-ERROR-001`
- `E1-BR-001`, `E1-BR-004`, `E1-BR-016`, `E1-BR-017`
- `E1-SEC-001`, `E1-SEC-003`, `E1-SEC-006`, `E1-SEC-007`, `E1-SEC-008`
- `HTTP-CONTRACT-000`, `HTTP-CONTRACT-001`, `HTTP-CONTRACT-003`,
  `HTTP-CONTRACT-006`, `ERROR-STD-002`, `VAL-STD-001`, `VAL-STD-003`,
  `VAL-STD-005`
