# Story 1.7: Manage supplementary CV sections — Verification

Return to the [story overview](README.md). Shared layer expectations and integration gates come from [Epic test strategy](../../test-strategy.md).

## Acceptance-criterion traceability

| Acceptance criterion | Covering tasks |
| --- | --- |
| `AC-1-7-manage-supplementary-cv-sections-01` | `TASK-1-7-01`, `TASK-1-7-02`, `TASK-1-7-03`, `TASK-1-7-04`, `TASK-1-7-05`, `TASK-1-7-06` |
| `AC-1-7-manage-supplementary-cv-sections-02` | `TASK-1-7-01`, `TASK-1-7-02`, `TASK-1-7-03`, `TASK-1-7-04`, `TASK-1-7-05`, `TASK-1-7-06` |
| `AC-1-7-manage-supplementary-cv-sections-03` | `TASK-1-7-01`, `TASK-1-7-02`, `TASK-1-7-03`, `TASK-1-7-04`, `TASK-1-7-05`, `TASK-1-7-06` |

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

Reserve shared Profile files through `E1-COORD-PROFILE-001`; task 05 waits for
the `E1-COORD-VERSION-001` persistence fixture checkpoint; task 06 uses
`E1-COORD-TEST-001` and gates all three ACs.

The story may leave review only when every canonical AC has passing evidence, every task is `done`, shared coordination records are resolved, and no open decision can change behavior, security, data semantics, public contracts, dependencies, or verification.
