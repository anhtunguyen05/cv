# Story 1.8: Create and view an immutable CV Version — Verification

Return to the [story overview](README.md). Shared layer expectations and integration gates come from [Epic test strategy](../../test-strategy.md).

## Acceptance-criterion traceability

| Acceptance criterion | Covering tasks |
| --- | --- |
| `AC-1-8-create-and-view-an-immutable-cv-version-01` | `TASK-1-8-01`, `TASK-1-8-02`, `TASK-1-8-03`, `TASK-1-8-04`, `TASK-1-8-05`, `TASK-1-8-06`, `TASK-1-8-07`, `TASK-1-8-08` |
| `AC-1-8-create-and-view-an-immutable-cv-version-02` | `TASK-1-8-01`, `TASK-1-8-02`, `TASK-1-8-03`, `TASK-1-8-04`, `TASK-1-8-05`, `TASK-1-8-06`, `TASK-1-8-07`, `TASK-1-8-08` |
| `AC-1-8-create-and-view-an-immutable-cv-version-03` | `TASK-1-8-01`, `TASK-1-8-02`, `TASK-1-8-04`, `TASK-1-8-05`, `TASK-1-8-06`, `TASK-1-8-07`, `TASK-1-8-08` |
| `AC-1-8-create-and-view-an-immutable-cv-version-04` | `TASK-1-8-01`, `TASK-1-8-02`, `TASK-1-8-03`, `TASK-1-8-04`, `TASK-1-8-05`, `TASK-1-8-06`, `TASK-1-8-07`, `TASK-1-8-08` |
| `AC-1-8-create-and-view-an-immutable-cv-version-05` | `TASK-1-8-01`, `TASK-1-8-02`, `TASK-1-8-03`, `TASK-1-8-04`, `TASK-1-8-05`, `TASK-1-8-06`, `TASK-1-8-07`, `TASK-1-8-08` |

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

Consume the frozen Profile schema through `E1-COORD-PROFILE-001`, reserve all
snapshot boundaries through `E1-COORD-VERSION-001`, and use
`E1-COORD-TEST-001` for browser tooling. Tasks 07–08 gate all five ACs.

The story may leave review only when every canonical AC has passing evidence, every task is `done`, shared coordination records are resolved, and no open decision can change behavior, security, data semantics, public contracts, dependencies, or verification.
