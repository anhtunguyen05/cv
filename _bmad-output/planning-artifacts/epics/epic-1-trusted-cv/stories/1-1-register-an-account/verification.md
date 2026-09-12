# Story 1.1: Register an account — Verification

Return to the [story overview](README.md). Shared layer expectations and integration gates come from [Epic test strategy](../../test-strategy.md).

## Acceptance-criterion traceability

| Acceptance criterion | Covering tasks |
| --- | --- |
| `AC-1-1-register-an-account-01` | `TASK-1-1-01`, `TASK-1-1-02`, `TASK-1-1-03`, `TASK-1-1-04`, `TASK-1-1-06`, `TASK-1-1-07`, `TASK-1-1-08`, `TASK-1-1-09`, `TASK-1-1-10` |
| `AC-1-1-register-an-account-02` | `TASK-1-1-01`, `TASK-1-1-03`, `TASK-1-1-04`, `TASK-1-1-05`, `TASK-1-1-06`, `TASK-1-1-07`, `TASK-1-1-08`, `TASK-1-1-09`, `TASK-1-1-10` |
| `AC-1-1-register-an-account-03` | `TASK-1-1-01`, `TASK-1-1-03`, `TASK-1-1-04`, `TASK-1-1-05`, `TASK-1-1-06`, `TASK-1-1-07`, `TASK-1-1-08`, `TASK-1-1-09`, `TASK-1-1-10` |
| `AC-1-1-register-an-account-04` | `TASK-1-1-01`, `TASK-1-1-03`, `TASK-1-1-04`, `TASK-1-1-06`, `TASK-1-1-07`, `TASK-1-1-08`, `TASK-1-1-09`, `TASK-1-1-10` |
| `AC-1-1-register-an-account-05` | `TASK-1-1-01`, `TASK-1-1-02`, `TASK-1-1-04`, `TASK-1-1-06`, `TASK-1-1-07`, `TASK-1-1-08`, `TASK-1-1-09`, `TASK-1-1-10` |

## Required evidence layers

| Layer | Evidence required before completion |
| --- | --- |
| Unit/domain | Rules, normalization, invariants, and failure classification relevant to this story |
| API/feature | Request/response/status/error contract, authentication, authorization, and meaningful failures |
| Persistence/integration | Atomicity, constraints, ownership isolation, concurrency, reload, and rollback against the declared database |
| Frontend component/static | Schema mapping, loading/submitting/success/failure states, accessibility, stale state, and duplicate action behavior |
| FE/API integration | Shared fixtures prove both sides consume one contract and one error-path convention |
| End to end | Critical User journey plus meaningful failure/recovery paths on isolated disposable data |

Mark a layer `N/A` only with a written reason tied to an AC. Planned commands are not evidence; replace each task's `Verification` clause with the actual command/result or review evidence before `done`.

## Exit gate

Use `E1-COORD-AUTH-001` for every shared auth/User file and
`E1-COORD-TEST-001` for reusable test tooling. Story acceptance requires tasks
08–10 and evidence for every canonical AC; local contract variants are rejected.

The story may leave review only when every canonical AC has passing evidence, every task is `done`, shared coordination records are resolved, and no open decision can change behavior, security, data semantics, public contracts, dependencies, or verification.
