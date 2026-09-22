# Story 2.3: Analyze a Job Description — Verification

Return to the [Story overview](README.md) and [Epic test strategy](../../test-strategy.md).

## Acceptance-criterion traceability

| Acceptance criterion | Covering tasks |
| --- | --- |
| `AC-2-3-analyze-a-job-description-01` | `TASK-2-3-01`, `TASK-2-3-02`, `TASK-2-3-03` |
| `AC-2-3-analyze-a-job-description-02` | `TASK-2-3-01`, `TASK-2-3-02`, `TASK-2-3-03` |
| `AC-2-3-analyze-a-job-description-03` | `TASK-2-3-01`, `TASK-2-3-02`, `TASK-2-3-03` |
| `AC-2-3-analyze-a-job-description-04` | `TASK-2-3-01`, `TASK-2-3-02`, `TASK-2-3-03` |

## Required evidence

- Corpus/domain: all supported signal groups, absent/unknown, aliases,
  duplicates, ordering, Unicode/boundaries, and repeat-process equality.
- Application/PostgreSQL: exact revision/rule pinning, deterministic uniqueness,
  current/deleted race, rollback, and no successful partial output.
- API/policy: contract, two-User non-disclosure, repeat response, throttle,
  retryable/terminal failures, and sanitized logs.
- Frontend/E2E: raw-versus-derived labeling, accessible groups, loading/stale/
  retry/failure states, reload, and no stale success after a new revision.

## Exit gate

All ACs need passing evidence and every task must be `done`. The versioned
corpus and rule identifier are part of evidence; “same result once” is not a
sufficient repeatability claim.
