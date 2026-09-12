# Story 1.3: Create a CV Profile — Contract

Return to the [story overview](README.md). This file owns only the Story-specific contract slice. It inherits [Epic contracts](../../contracts.md) and global HTTP/error standards; unresolved values remain owned by [Epic decisions](../../decisions.md).

## Operation matrix

| Operation | Proposed HTTP boundary | Request responsibility | Success responsibility | Failure responsibility |
| --- | --- | --- | --- | --- |
| Create Profile | `POST /api/v1/cv-profiles` | Title and approved personal-information fields; never owner ID | `201` with `data` matching `E1-CONTRACT-PROFILE-001` | Field validation, duplicate/lost-response reconciliation, auth, and server failure |
| Profile detail | `GET /api/v1/cv-profiles/{profile}` | Session-derived ownership and server Profile ID | `200` with owned Profile resource | Non-disclosing `RESOURCE_NOT_FOUND` for absent or foreign Profile |

`E1-DEC-003` through `E1-DEC-005`, `E1-DEC-007`, and `E1-DEC-008` freeze fields, limits, persistence shape, concurrency, route details, and navigation.

## Frontend/API integration rules

- The frontend sends only approved fields and derives authenticated ownership from the browser session.
- Success data is accepted only after schema validation against the shared Epic representation.
- Field and global errors map by stable code/path; message text is presentation, not control flow.
- Retryable, terminal, stale, expired-session, and ambiguous-success outcomes remain distinct.
- A story implementation may not introduce a local envelope, error code, User/Profile/Version shape, or authentication mechanism.

## Approval boundary

Before this story moves to `ready-for-dev`, every decision referenced by this contract must contain an approved resolution, owner, and dated evidence. The stable subset is then promoted to `docs/contracts/`; executable fixtures consume that source instead of redefining it here.
