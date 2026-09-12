# Story 1.5: Manage education and experience — Contract

Return to the [story overview](README.md). This file owns only the Story-specific contract slice. It inherits [Epic contracts](../../contracts.md) and global HTTP/error standards; unresolved values remain owned by [Epic decisions](../../decisions.md).

## Operation matrix

| Operation | HTTP boundary | Request responsibility | Success responsibility | Failure responsibility |
| --- | --- | --- | --- | --- |
| Add/edit/remove education | Profile history write operation selected by `E1-DEC-004` | Approved item fields, server item ID for edit/remove, and concurrency token when required | Updated shared Profile resource | Nested validation, invalid chronology, stale conflict, auth, and hidden Profile |
| Add/edit/remove experience | Same approved Profile history boundary | Approved item fields and stable server item identity | Updated shared Profile resource | Nested validation, invalid chronology, stale conflict, auth, and hidden Profile |

All item paths and shapes come from `E1-CONTRACT-PROFILE-001`. `E1-DEC-003` through `E1-DEC-005` freeze item fields, date semantics, limits, persistence, and write granularity.

## Frontend/API integration rules

- The frontend sends only approved fields and derives authenticated ownership from the browser session.
- Success data is accepted only after schema validation against the shared Epic representation.
- Field and global errors map by stable code/path; message text is presentation, not control flow.
- Retryable, terminal, stale, expired-session, and ambiguous-success outcomes remain distinct.
- A story implementation may not introduce a local envelope, error code, User/Profile/Version shape, or authentication mechanism.

## Approval boundary

Before this story moves to `ready-for-dev`, every decision referenced by this contract must contain an approved resolution, owner, and dated evidence. The stable subset is then promoted to `docs/contracts/`; executable fixtures consume that source instead of redefining it here.
