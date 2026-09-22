# Story 5.2 — Verification

## Acceptance-criterion traceability

| Acceptance criterion | Covering tasks |
| --- | --- |
| `AC-5-2-handle-provider-and-background-failures-01` | `TASK-5-2-01`, `TASK-5-2-02` |
| `AC-5-2-handle-provider-and-background-failures-02` | `TASK-5-2-01`, `TASK-5-2-03` |

Completion requires bounded provider retry, terminal malformed output, named
async lifecycle only, lease/cancel/late/duplicate/lost races, atomic valid result,
truthful accessible state, sanitized audit/metrics/runbook, PostgreSQL fault injection,
and E2E. Without an approved async consumer, AC2 remains blocked rather than simulated as done.
