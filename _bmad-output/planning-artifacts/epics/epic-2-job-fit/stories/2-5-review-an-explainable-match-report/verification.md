# Story 2.5: Review an explainable Match Report — Verification

Return to the [Story overview](README.md) and [Epic test strategy](../../test-strategy.md).

## Acceptance-criterion traceability

| Acceptance criterion | Covering tasks |
| --- | --- |
| `AC-2-5-review-an-explainable-match-report-01` | `TASK-2-5-01` through `TASK-2-5-07` |
| `AC-2-5-review-an-explainable-match-report-02` | `TASK-2-5-01` through `TASK-2-5-07` |
| `AC-2-5-review-an-explainable-match-report-03` | `TASK-2-5-01` through `TASK-2-5-07` |
| `AC-2-5-review-an-explainable-match-report-04` | `TASK-2-5-01`, `TASK-2-5-03`, `TASK-2-5-04`, `TASK-2-5-06`, `TASK-2-5-07` |

## Required evidence

- Backend/contract: owner-only list/detail, exact stored/pinned projection,
  deleted-source context, immutable results, invalid stored schema, safe logs.
- Quality truthfulness: Weak Evidence and missing counterexamples cannot render
  as matched evidence or unsupported claims.
- Frontend/accessibility: semantic headings/lists, non-color-only score/groups,
  labels, focus, keyboard order, long/empty content, safe source rendering.
- End to end: create report, open/reload, review exact sources and groups, revise
  then delete the JD, reopen unchanged report, and reject foreign access.

## Exit gate

All four ACs and every task require passing evidence. Browser review must use
the stored fixture result and prove no client-side reclassification or live
source substitution occurred.
