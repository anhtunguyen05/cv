# Story 1.4: Manage CV summary and skills — Contract

Return to the [story overview](README.md). This file owns only the Story-specific contract slice. It inherits [Epic contracts](../../contracts.md) and global HTTP/error standards; unresolved values remain owned by [Epic decisions](../../decisions.md).

## Operation matrix

| Operation | HTTP boundary | Request responsibility | Success responsibility | Failure responsibility |
| --- | --- | --- | --- | --- |
| Update summary | Profile write operation selected by `E1-DEC-004` | Approved summary value plus concurrency token when required | Updated shared Profile resource | Field validation, stale-write conflict, auth, hidden Profile, and server failure |
| Update skills | Same approved Profile write boundary | Approved category/skill structure with stable ordering | Updated shared Profile resource | Nested field errors, stale-write conflict, auth, hidden Profile, and server failure |

The story may not invent a local Profile route or representation. It consumes `E1-CONTRACT-PROFILE-001`; `E1-DEC-003` through `E1-DEC-005` freeze the write granularity and exact payload.

## Frontend/API integration rules

- The frontend sends only approved fields and derives authenticated ownership from the browser session.
- Success data is accepted only after schema validation against the shared Epic representation.
- Field and global errors map by stable code/path; message text is presentation, not control flow.
- Retryable, terminal, stale, expired-session, and ambiguous-success outcomes remain distinct.
- A story implementation may not introduce a local envelope, error code, User/Profile/Version shape, or authentication mechanism.

## Approval boundary

Before this story moves to `ready-for-dev`, every decision referenced by this contract must contain an approved resolution, owner, and dated evidence. The stable subset is then promoted to `docs/contracts/`; executable fixtures consume that source instead of redefining it here.
