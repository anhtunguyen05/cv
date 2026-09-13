# Story 2.2: Manage saved Job Descriptions — Contract

Return to the [Story overview](README.md). Shared representations live in
[Epic contracts](../../contracts.md).

## Operation matrix

| Operation | Proposed boundary | Request responsibility | Success responsibility | Failure responsibility |
| --- | --- | --- | --- | --- |
| List active JDs | `GET /api/v1/job-descriptions` | Approved pagination/filter only | `200` owned active collection with deterministic order | Auth/session and safe collection failure |
| Update JD | `PATCH /api/v1/job-descriptions/{jobDescription}` | Changed approved fields plus current revision/precondition | `200` with same logical ID and new immutable current revision | Validation, stale conflict, deleted/not found, throttle, ambiguous success |
| Delete JD | `DELETE /api/v1/job-descriptions/{jobDescription}` | Path ID plus approved precondition | `204` or approved idempotent terminal response | Stale/deleted/not found, auth/session, unexpected failure |
| Resolve historical report source | Consumed through Match Report detail | No arbitrary foreign revision query | Stored source summary includes exact revision and deleted-parent state | Non-disclosing not found |

The update response never implies that prior Analysis moved to the new
revision. Exact status, no-op, repeat-delete, history, pagination, and
precondition behavior are owned by `E2-DEC-001`, `E2-DEC-003`, and
`E2-DEC-008`.

## FE/API integration rules

- The client invalidates active list and logical-detail caches after confirmed
  update/delete while retaining locally entered text on stale/failure outcomes.
- A successful update selects the returned current revision; it clears any
  current-analysis success state rather than reusing cached prior Analysis.
- A deleted item disappears from active navigation. Existing report routes use
  their pinned source summary and show deleted context without reviving actions.
- UI uses stable code/path/precondition metadata; it does not parse message
  prose or infer deletion from an empty response.

## Approval boundary

The `E2-COORD-JD-001` checkpoint and referenced decisions must be approved and
promoted before this Story becomes ready for development. Report-source fields
must also agree with `E2-COORD-MATCH-001`.
