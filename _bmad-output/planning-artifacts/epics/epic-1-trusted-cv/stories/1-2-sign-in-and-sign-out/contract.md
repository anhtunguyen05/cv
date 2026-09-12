# Story 1.2: Sign in and sign out — Contract

Return to the [story overview](README.md). This file owns only the Story-specific contract slice. It inherits [Epic contracts](../../contracts.md) and global HTTP/error standards; unresolved values remain owned by [Epic decisions](../../decisions.md).

## Operation matrix

| Operation | Proposed HTTP boundary | Request responsibility | Success responsibility | Failure responsibility |
| --- | --- | --- | --- | --- |
| Sign in | `POST /api/v1/auth/login` | Canonical email and password only | `200` with shared `data.user` and regenerated session | Identical invalid-credential response, throttle, CSRF/session, and unexpected failure |
| Current account | Reuse `GET /api/v1/auth/me` from Story 1.1 | Approved browser session | `200` with shared `data.user`, private/no-store | `UNAUTHENTICATED` and protected-state clearing |
| Sign out | `POST /api/v1/auth/logout` | Approved browser session and CSRF boundary | `204` with session invalidated | Idempotent or unauthenticated outcome frozen by decision |

Story 1.2 must reuse `E1-CONTRACT-USER-001` and the current-account operation owned by Story 1.1. `E1-DEC-001`, `E1-DEC-002`, `E1-DEC-007`, and `E1-DEC-009` freeze exact login/logout behavior.

## Frontend/API integration rules

- The frontend sends only approved fields and derives authenticated ownership from the browser session.
- Success data is accepted only after schema validation against the shared Epic representation.
- Field and global errors map by stable code/path; message text is presentation, not control flow.
- Retryable, terminal, stale, expired-session, and ambiguous-success outcomes remain distinct.
- A story implementation may not introduce a local envelope, error code, User/Profile/Version shape, or authentication mechanism.

## Approval boundary

Before this story moves to `ready-for-dev`, every decision referenced by this contract must contain an approved resolution, owner, and dated evidence. The stable subset is then promoted to `docs/contracts/`; executable fixtures consume that source instead of redefining it here.
