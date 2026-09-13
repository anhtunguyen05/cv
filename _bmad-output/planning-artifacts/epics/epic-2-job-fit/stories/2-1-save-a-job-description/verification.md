# Story 2.1: Save a Job Description — Verification

Return to the [Story overview](README.md). Shared expectations come from the
[Epic test strategy](../../test-strategy.md).

## Acceptance-criterion traceability

| Acceptance criterion | Covering tasks |
| --- | --- |
| `AC-2-1-save-a-job-description-01` | `TASK-2-1-01` through `TASK-2-1-08` |
| `AC-2-1-save-a-job-description-02` | `TASK-2-1-01` through `TASK-2-1-08` |
| `AC-2-1-save-a-job-description-03` | `TASK-2-1-01`, `TASK-2-1-03`, `TASK-2-1-04`, `TASK-2-1-05`, `TASK-2-1-07`, `TASK-2-1-08` |

## Required evidence

- Unit/application: normalization, aggregate/revision invariants, dedupe, and
  failure classification.
- API/policy: exact contract, session, two-User non-disclosure, malformed input,
  rate limit, and safe serialization.
- MySQL: migration, root/revision atomicity, constraints, rollback, concurrency,
  and exact reload.
- Frontend: schema/error mapping, pending/success/failure/reconciliation, safe
  rendering, validation focus, and duplicate submission.
- End to end: save, reload, invalid input, lost/retry state, and foreign ID on
  isolated disposable data.

## Exit gate

Every AC needs passing evidence, every task must be `done`, and the approved
`E2-COORD-JD-001` checkpoint must be consumable without a local contract
variant. Planned commands are replaced by actual command/result evidence before
completion.
