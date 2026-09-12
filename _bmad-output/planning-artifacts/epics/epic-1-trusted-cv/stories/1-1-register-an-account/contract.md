# Story 1.1: Register an account — Contract

Return to the [story overview](README.md). This file owns only the Story-specific contract slice. It inherits [Epic contracts](../../contracts.md) and global HTTP/error standards; unresolved values remain owned by [Epic decisions](../../decisions.md).

## Operation matrix

| Operation | Proposed HTTP boundary | Request responsibility | Success responsibility | Failure responsibility |
| --- | --- | --- | --- | --- |
| Register | `POST /api/v1/auth/register` | Name, canonical email, password, and approved confirmation only | `201` with `data.user` and the approved authenticated session | Field validation, duplicate-safe outcome, throttle, CSRF/session, and unexpected failure |
| Current account | `GET /api/v1/auth/me` | Approved browser session; no user ID payload | `200` with the same `data.user`, private/no-store | `UNAUTHENTICATED` without account disclosure |

`E1-DEC-001`, `E1-DEC-002`, `E1-DEC-007`, and `E1-DEC-009` freeze the exact topology, fields, status/error matrix, retry classification, and post-registration navigation.

## Frontend/API integration rules

- The frontend sends only approved fields and derives authenticated ownership from the browser session.
- Success data is accepted only after schema validation against the shared Epic representation.
- Field and global errors map by stable code/path; message text is presentation, not control flow.
- Retryable, terminal, stale, expired-session, and ambiguous-success outcomes remain distinct.
- A story implementation may not introduce a local envelope, error code, User/Profile/Version shape, or authentication mechanism.

## Approval boundary

Before this story moves to `ready-for-dev`, every decision referenced by this contract must contain an approved resolution, owner, and dated evidence. The stable subset is then promoted to `docs/contracts/`; executable fixtures consume that source instead of redefining it here.
