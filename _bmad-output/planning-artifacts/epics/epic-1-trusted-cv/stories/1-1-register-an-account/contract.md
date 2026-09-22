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

## TASK-1-1-01 fixture boundary

The contract document and executable fixture corpus produced by
`TASK-1-1-01` are the cross-layer source for the registration and
current-account examples. They must pin, after approval of the blocking Epic
decisions, the request fields and normalization, success and failure status,
the common envelope, stable `code`, field-keyed `details`, relevant cache and
CSRF/session behavior, and the client action for each matrix row. The corpus
must include valid, duplicate/concurrent duplicate, invalid/missing/password
policy, malformed, throttled, authenticated-caller, session/CSRF failure,
lost-response reconciliation, current-account success, and unauthenticated
cases.

Fixtures are synthetic and versioned. They must assert that the public User
projection contains only the approved public fields and never contains a
password, password hash, remember token, bearer token, session cookie, CSRF
material, or internal security metadata. Backend and frontend tasks consume
these fixtures; neither may define a local response envelope or authentication
mechanism. The current scaffold's `/auth/*` plus bearer-token shape is not an
approved target and must be treated as a migration input, not copied into the
fixture contract.

## Approval boundary

Before this story moves to `ready-for-dev`, every decision referenced by this contract must contain an approved resolution, owner, and dated evidence. The stable subset is then promoted to `docs/contracts/`; executable fixtures consume that source instead of redefining it here.
