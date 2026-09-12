# Story 3.2: Preview a saved CV Version — Verification

## AC-to-task traceability

| Acceptance criterion | Tasks |
| --- | --- |
| `AC-3-2-preview-a-saved-cv-version-01` | `TASK-3-2-01` through `TASK-3-2-08` |
| `AC-3-2-preview-a-saved-cv-version-02` | `TASK-3-2-01` through `TASK-3-2-04`, `TASK-3-2-06`, `TASK-3-2-08` |
| `AC-3-2-preview-a-saved-cv-version-03` | `TASK-3-2-01` through `TASK-3-2-08` |
| `AC-3-2-preview-a-saved-cv-version-04` | `TASK-3-2-01`, `TASK-3-2-03` through `TASK-3-2-05`, `TASK-3-2-07`, `TASK-3-2-08` |

## Required evidence

- Backend/contract: exact owned Version, compatible Template version, schema,
  safe projection, non-disclosure, no Profile read/mutation, and failures.
- Frontend: route/query/adapters, complete states, shared section mapping,
  empty/long/Unicode/unsafe data, links, dates, and renderer exceptions.
- Accessibility/visual: semantic outline, keyboard/focus, names, reflow, overflow,
  and approved fixture baselines with content assertions.
- Browser: select/open/reload, Profile draft divergence, foreign/missing source,
  stale Template, safe retry, and disposable two-User reset.

## Exit gate

- All four canonical ACs have passing evidence tied to exact source and fixture versions.
- `E3-COORD-RENDER-001` records the accepted reusable Preview/Export renderer boundary.
- `E3-PREREQ-VERSION-001` and all Story blockers are closed before implementation completion.
- Task completion records command/result or review evidence; plans alone do not count.
