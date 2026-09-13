# Story 2.4: Generate a Match Report — Verification

Return to the [Story overview](README.md) and [Epic test strategy](../../test-strategy.md).

## Acceptance-criterion traceability

| Acceptance criterion | Covering tasks |
| --- | --- |
| `AC-2-4-generate-a-match-report-01` | `TASK-2-4-01` through `TASK-2-4-08` |
| `AC-2-4-generate-a-match-report-02` | `TASK-2-4-01`, `TASK-2-4-03` through `TASK-2-4-08` |
| `AC-2-4-generate-a-match-report-03` | `TASK-2-4-01`, `TASK-2-4-03` through `TASK-2-4-08` |
| `AC-2-4-generate-a-match-report-04` | `TASK-2-4-01`, `TASK-2-4-03` through `TASK-2-4-08` |
| `AC-2-4-generate-a-match-report-05` | `TASK-2-4-01`, `TASK-2-4-03` through `TASK-2-4-08` |
| `AC-2-4-generate-a-match-report-06` | `TASK-2-4-01` through `TASK-2-4-05`, `TASK-2-4-07`, `TASK-2-4-08` |

## Required evidence

- Corpus/domain: score/classification/recommendation truthfulness, unsupported
  claims, Weak Evidence thresholds, rounding/order, aliases, and repeat equality.
- Application/MySQL: exact source/rule pins, current Analysis precondition,
  historical/deleted/mixed-owner rejection, source races, dedupe, and rollback.
- API/policy: complete immutable resource, stable conflict/error matrix, private
  caches, rate limits, malformed transport, and safe logs.
- Frontend/E2E: source selection, Analyze-first action, pending/conflict/retry/
  terminal/success states, exact navigation, and repeat result comparison.

## Exit gate

All six ACs, every task, the versioned evaluation corpus, and its approved
quality threshold require evidence. Determinism and score quality are tested
separately; neither substitutes for the other.
