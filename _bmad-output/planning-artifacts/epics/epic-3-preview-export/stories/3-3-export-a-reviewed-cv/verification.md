# Story 3.3: Export a reviewed CV — Verification

## AC-to-task traceability

| Acceptance criterion | Tasks |
| --- | --- |
| `AC-3-3-export-a-reviewed-cv-01` | `TASK-3-3-01` through `TASK-3-3-07` |
| `AC-3-3-export-a-reviewed-cv-02` | `TASK-3-3-01` through `TASK-3-3-04`, `TASK-3-3-06`, `TASK-3-3-07` |
| `AC-3-3-export-a-reviewed-cv-03` | `TASK-3-3-01`, `TASK-3-3-05`, `TASK-3-3-07` |

## Required evidence

- State/component: source readiness/equality, invalidation, preparation,
  repeated click, cleanup, unsupported/blocked print, return/cancel, retry.
- Security/integration: owned Version only, non-disclosure, no foreign content,
  safe print surface/assets, and no AI/provider/worker network dependency.
- Print: exact content/order, empty/long/Unicode/markup fixtures, page breaks,
  hidden controls, links, title hints, and approved browser/page matrix.
- Browser: review then invoke print, draft divergence, source race, failure/retry,
  foreign source, keyboard/focus return, and disposable fixture reset.

## Exit gate

- All three canonical ACs have passing evidence tied to exact source and fixture versions.
- `E3-COORD-PRINT-001` records accepted print/browser boundaries and baselines.
- No server PDF, stored artifact, AI, or worker work is smuggled into completion.
- Task completion records command/result or review evidence; plans alone do not count.
