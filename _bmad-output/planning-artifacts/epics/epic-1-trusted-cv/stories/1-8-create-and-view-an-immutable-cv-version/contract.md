# Story 1.8: Create and view an immutable CV Version — Contract

Return to the [story overview](README.md). This file owns only the Story-specific contract slice. It inherits [Epic contracts](../../contracts.md) and global HTTP/error standards; unresolved values remain owned by [Epic decisions](../../decisions.md).

## Operation matrix

| Operation | Proposed HTTP boundary | Request responsibility | Success responsibility | Failure responsibility |
| --- | --- | --- | --- | --- |
| Create Version | `POST /api/v1/cv-profiles/{profile}/versions` | Owned Profile ID, valid name, and approved source/concurrency precondition | `201` with immutable Version metadata/resource | Validation, hidden Profile, non-versionable/stale source, auth, and server failure |
| List Versions | `GET /api/v1/cv-versions` or approved Profile-scoped equivalent | Approved filters/pagination only | Deterministic newest-first collection | Auth and safe collection failure |
| Version detail | `GET /api/v1/cv-versions/{version}` | Server Version ID and session-derived ownership | `200` reconstructed only from stored snapshot | Non-disclosing `RESOURCE_NOT_FOUND` for absent or foreign Version |

`E1-DEC-004` and `E1-DEC-006` freeze routes, naming, snapshot schema/version, compatibility, list scope, and source preconditions. Later Profile edits must never alter these responses.

## Frontend/API integration rules

- The frontend sends only approved fields and derives authenticated ownership from the browser session.
- Success data is accepted only after schema validation against the shared Epic representation.
- Field and global errors map by stable code/path; message text is presentation, not control flow.
- Retryable, terminal, stale, expired-session, and ambiguous-success outcomes remain distinct.
- A story implementation may not introduce a local envelope, error code, User/Profile/Version shape, or authentication mechanism.

## Approval boundary

Before this story moves to `ready-for-dev`, every decision referenced by this contract must contain an approved resolution, owner, and dated evidence. The stable subset is then promoted to `docs/contracts/`; executable fixtures consume that source instead of redefining it here.
