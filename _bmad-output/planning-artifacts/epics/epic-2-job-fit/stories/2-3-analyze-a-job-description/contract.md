# Story 2.3: Analyze a Job Description — Contract

Return to the [Story overview](README.md). Shared Analysis representation and
errors live in [Epic contracts](../../contracts.md).

## Operation matrix

| Operation | Proposed boundary | Request responsibility | Success responsibility | Failure responsibility |
| --- | --- | --- | --- | --- |
| Analyze current revision | `POST /api/v1/job-descriptions/{jobDescription}/analyses` | Path logical JD plus approved current-revision/dedupe precondition; no signal output fields | `200` existing or `201` new immutable `data` Analysis per approved repeat policy | Analysis required conflicts, deleted/stale/not found, throttle, retryable/terminal failure |
| Read Analysis | Approved nested current/historical route | Owned logical/revision or Analysis ID per topology | `200` exact stored revision/rule result, private cache behavior | Non-disclosing not found, auth/session, unexpected failure |

No success response may omit a required signal field, source ID, rule version,
or schema version. `E2-DEC-001`, `E2-DEC-004`, and `E2-DEC-007` freeze exact
routes, repeat status, request tokens, headers, errors, and retry classification.

## FE/API integration rules

- The frontend never performs authoritative extraction; it validates and
  displays the server's versioned Analysis fixture.
- Query keys include logical JD, exact revision, and applicable Analysis/rule
  identity so a new revision cannot display prior success as current.
- Absent, unknown, and empty values map to distinct approved presentation where
  semantics differ; none becomes fabricated placeholder content.
- A retryable failure preserves raw/detail state and offers one controlled retry.
- A successful result is displayed only after full schema validation; invalid
  payloads become terminal contract errors, not partial UI success.

## Approval boundary

The revision and Analysis coordination checkpoints plus all referenced
decisions must be approved. Versioned analysis fixtures are promoted and shared
between Laravel and Vue verification before `ready-for-dev`.
