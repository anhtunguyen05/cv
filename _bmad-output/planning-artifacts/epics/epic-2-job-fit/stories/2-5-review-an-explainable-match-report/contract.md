# Story 2.5: Review an explainable Match Report — Contract

Return to the [Story overview](README.md). The immutable stored representation
comes from [Epic contracts](../../contracts.md); this slice owns read projection
and presentation responsibilities.

## Operation matrix

| Operation | Proposed boundary | Request responsibility | Success responsibility | Failure responsibility |
| --- | --- | --- | --- | --- |
| List owned Match Reports | `GET /api/v1/match-reports` | Approved pagination/filter only | `200` deterministic owned summary collection | Auth/session and safe collection failure |
| Read Match Report | `GET /api/v1/match-reports/{matchReport}` | Report ULID plus owner session | `200` exact stored output, pinned source/rule summary, deleted-parent context, private cache | Non-disclosing not found, auth/session, invalid stored schema, unexpected failure |

The resource contains stable machine fields for score, groups, evidence/source
references, related CV section, and source status; visible prose is not a client
control signal. `E2-DEC-001`, `E2-DEC-005`, `E2-DEC-006`, and `E2-DEC-008`
freeze exact projection, ordering, pagination, labels, and empty/deleted behavior.

## FE/API integration rules

- Query/cache identity is immutable Match Report ID plus approved representation
  version; current Profile/JD cache changes cannot rewrite the view.
- The frontend validates the whole stored projection before rendering any score
  or classification group.
- Matched, missing, and Weak Evidence render from explicit server fields. The
  client does not infer category from message text or evidence length.
- Related section references use approved stable section IDs and safe
  navigation/display semantics; they do not apply edits.
- Deleted source status changes available actions, not the stored report result.

## Approval boundary

The Story 2.4 immutable report checkpoint, Epic 1 CV Version consumer contract,
and referenced explanation/history decisions must be approved and promoted to
shared fixtures before `ready-for-dev`.
