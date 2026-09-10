# Epic 1 Shared Contracts

These contracts are proposed planning agreements. Global HTTP envelope,
versioning, field naming, timestamp, error, and framework-failure behavior come
from `docs/contracts/common/http.md` and are not repeated here. Open values are
owned by `decisions.md`.

## E1-CONTRACT-USER-001 — Public User

```json
{
  "id": "existing Laravel user identifier serialized per the approved API decision",
  "name": "display name",
  "email": "canonical account email"
}
```

The shape is reused by registration, sign-in, and current-account responses.
It excludes password, password hash, remember token, cookies, CSRF material,
and internal verification/security metadata.

## E1-CONTRACT-AUTH-001 — Browser account access

| Operation | Proposed route | Success | Shared failure behavior |
| --- | --- | --- | --- |
| CSRF bootstrap | Sanctum-approved bootstrap route | Cookie/state established | `SESSION_EXPIRED` or safe bootstrap failure |
| Register | `POST /api/v1/auth/register` | `201`, `data.user` | validation, duplicate, rate limit, session failure |
| Sign in | `POST /api/v1/auth/login` | `200`, `data.user` | generic invalid credentials, rate limit, session failure |
| Current account | `GET /api/v1/auth/me` | `200`, `data.user`, private/no-store | `UNAUTHENTICATED` |
| Sign out | `POST /api/v1/auth/logout` | `204` | `UNAUTHENTICATED` or idempotent success per decision |

All browser requests use the Sanctum stateful cookie and CSRF boundary defined
by `AD-13` and `SEC-STD-001`/`SEC-STD-002`. Exact topology, routes, statuses,
field limits, duplicate semantics, throttle policy, and logout idempotency
remain open under `E1-DEC-001`, `E1-DEC-002`, and `E1-DEC-009`.

## E1-CONTRACT-PROFILE-001 — CV Profile resource

```json
{
  "id": "ULID",
  "title": "string",
  "personal_information": {},
  "summary": "string or null",
  "skills": {},
  "education": [],
  "experience": [],
  "projects": [],
  "certificates": [],
  "languages": [],
  "activities": [],
  "created_at": "UTC ISO-8601",
  "updated_at": "UTC ISO-8601"
}
```

- Each repeatable item contains a server-owned ULID `id`.
- `User` ownership is never accepted as a writable payload field.
- Create/update responses use `data`; validation uses field-keyed `details`
  whose keys follow the approved nested path convention.
- The exact fields, required/optional rules, content limits, write granularity,
  concurrency token, and list/detail endpoint set remain open decisions.

## E1-CONTRACT-VERSION-001 — Immutable CV Version

```json
{
  "id": "ULID",
  "name": "string",
  "source_profile_id": "ULID",
  "snapshot_schema_version": "string",
  "snapshot": {},
  "created_at": "UTC ISO-8601"
}
```

- Version creation accepts a User-owned Profile ID and valid Version name.
- `snapshot` contains the complete `E1-CONTRACT-PROFILE-001` content required
  for later consumers, without mutable Profile timestamps or ownership secrets
  unless the approved schema explicitly needs them.
- Detail and list responses never read live Profile content to reconstruct the
  Version.
- Collection responses follow `HTTP-CONTRACT-002` and sort newest first with a
  deterministic tie-breaker.

## E1-CONTRACT-ERROR-001 — Epic-specific failures

| Condition | Proposed status/code | Consumer action |
| --- | --- | --- |
| Duplicate registration email | `422` + field detail under `VALIDATION_FAILED` | Show generic email error |
| Invalid credentials | `401 INVALID_CREDENTIALS` | Show generic form error |
| Hidden/absent Profile or Version | `404 RESOURCE_NOT_FOUND` | Show non-disclosing not-found state |
| Stale Profile update | `409 PROFILE_UPDATE_CONFLICT` | Preserve edits and offer controlled reload/retry |
| Invalid Version source state | `409 PROFILE_NOT_VERSIONABLE` | Keep User on Profile/Version form with safe reason |

Malformed bodies, unauthenticated sessions, expired session/CSRF state,
throttling, missing routes, and unexpected failures reuse global codes. The
table is not final until the corresponding Epic decisions are approved.
