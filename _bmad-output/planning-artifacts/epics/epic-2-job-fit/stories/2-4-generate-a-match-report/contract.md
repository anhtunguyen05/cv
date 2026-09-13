# Story 2.4: Generate a Match Report — Contract

Return to the [Story overview](README.md). Shared Match representation and
failure codes live in [Epic contracts](../../contracts.md).

## Operation matrix

| Operation | Proposed boundary | Request responsibility | Success responsibility | Failure responsibility |
| --- | --- | --- | --- | --- |
| Create Match Report | `POST /api/v1/match-reports` | Owned `cv_version_id`, logical `job_description_id`, approved dedupe/precondition; no revision/analysis/output override | `201` with complete immutable `data` report and pinned source/rule IDs | Analysis-required, deleted/stale source, mixed-owner/not found, throttle, retryable/terminal failure |

The request names the logical JD; the server resolves its current revision and
successful Analysis in the accepted consistency boundary. A supplied
historical revision or Analysis override is rejected and never used for new
work. `E2-DEC-001`, `E2-DEC-005`
through `E2-DEC-007` freeze all exact matrices and result semantics.

## FE/API integration rules

- The web adapter validates every source/rule ID and complete output schema
  before treating creation as success.
- `JOB_DESCRIPTION_ANALYSIS_REQUIRED` provides a stable Analyze-first action;
  it is distinct from hidden/deleted/stale/terminal failure.
- Creation state stores the returned immutable report ID for Story 2.5; it does
  not derive a review projection from live JD or Profile cache values.
- The UI never computes authoritative score or reclassifies evidence; display
  formatting cannot change stored canonical values.
- Ambiguous create success is reconciled through the approved dedupe/list key
  before retrying.

## Approval boundary

Epic 1 Version, Epic 2 Analysis, matching, and deterministic test checkpoints
must all be approved. Stable source/result/error fixtures must be promoted and
consumed by both backend and frontend before `ready-for-dev`.
