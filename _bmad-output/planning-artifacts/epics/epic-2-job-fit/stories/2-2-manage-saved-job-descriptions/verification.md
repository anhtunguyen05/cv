# Story 2.2: Manage saved Job Descriptions — Verification

Return to the [Story overview](README.md) and [Epic test strategy](../../test-strategy.md).

## Acceptance-criterion traceability

| Acceptance criterion | Covering tasks |
| --- | --- |
| `AC-2-2-manage-saved-job-descriptions-01` | `TASK-2-2-01`, `TASK-2-2-02`, `TASK-2-2-04` through `TASK-2-2-08` |
| `AC-2-2-manage-saved-job-descriptions-02` | `TASK-2-2-01`, `TASK-2-2-02`, `TASK-2-2-04` through `TASK-2-2-08` |
| `AC-2-2-manage-saved-job-descriptions-03` | `TASK-2-2-01`, `TASK-2-2-03` through `TASK-2-2-08` |
| `AC-2-2-manage-saved-job-descriptions-04` | `TASK-2-2-01`, `TASK-2-2-03` through `TASK-2-2-08` |

## Required evidence

- Domain/MySQL: revision monotonicity/immutability, pointer atomicity, stale and
  competing updates, delete races, constraints, rollback, and historical pins.
- API/policy: list/update/delete/status/error/cache contract, two-User
  non-disclosure, repeat delete, deleted guards, and safe serialization.
- Frontend: current revision tracking, unsaved input on conflict, active-list
  invalidation, delete confirmation, disabled new-work actions, deleted banner.
- End to end: create, update, analyze/report fixture setup, update again, delete,
  and reopen the original report with unchanged sources.

## Exit gate

All four ACs need passing evidence and every task must be `done`. Historical
reproducibility is tested against stored source IDs and values, not assumed from
an HTTP success response.
