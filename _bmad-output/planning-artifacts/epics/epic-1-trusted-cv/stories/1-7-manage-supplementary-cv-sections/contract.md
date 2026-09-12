# Story 1.7: Manage supplementary CV sections — Contract

Return to the [story overview](README.md). This file owns only the Story-specific contract slice. It inherits [Epic contracts](../../contracts.md) and global HTTP/error standards; unresolved values remain owned by [Epic decisions](../../decisions.md).

## Operation matrix

| Operation | HTTP boundary | Request responsibility | Success responsibility | Failure responsibility |
| --- | --- | --- | --- | --- |
| Manage certificates | Profile section write operation selected by `E1-DEC-004` | Approved certificate fields and stable item identity | Updated shared Profile resource | Nested validation, stale conflict, auth, and hidden Profile |
| Manage languages | Same approved Profile section boundary | Approved language/proficiency values and stable item identity | Updated shared Profile resource | Nested validation, stale conflict, auth, and hidden Profile |
| Manage activities | Same approved Profile section boundary | Approved activity fields and stable item identity | Updated shared Profile resource | Nested validation, stale conflict, auth, and hidden Profile |

Optionality, paths, limits, ordering, and representations come from `E1-CONTRACT-PROFILE-001`; `E1-DEC-003` through `E1-DEC-005` freeze exact behavior.

## Frontend/API integration rules

- The frontend sends only approved fields and derives authenticated ownership from the browser session.
- Success data is accepted only after schema validation against the shared Epic representation.
- Field and global errors map by stable code/path; message text is presentation, not control flow.
- Retryable, terminal, stale, expired-session, and ambiguous-success outcomes remain distinct.
- A story implementation may not introduce a local envelope, error code, User/Profile/Version shape, or authentication mechanism.

## Approval boundary

Before this story moves to `ready-for-dev`, every decision referenced by this contract must contain an approved resolution, owner, and dated evidence. The stable subset is then promoted to `docs/contracts/`; executable fixtures consume that source instead of redefining it here.
