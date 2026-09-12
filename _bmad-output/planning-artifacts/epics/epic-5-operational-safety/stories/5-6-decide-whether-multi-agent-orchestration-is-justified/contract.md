# Story 5.6 — Contract Slice

Refines `E5-CONTRACT-ADR-001`.

| Artifact | Required content | Gate |
| --- | --- | --- |
| Measurement plan | Workloads/source, baseline versions/config/environment, metrics, threshold, repetitions, variance, data/privacy, owner | Approved before run |
| Result | Raw safe measurements, aggregate/variance/failures, reproducibility, limitation classification | Immutable/versioned; no cherry-picking |
| Alternatives review | Single-orchestrator tuning, workflow split, deterministic tools, queue, multi-agent; cost/risk/safety | Comparable assumptions |
| ADR | `adopt|defer`, evidence, invariants, ownership/tools/writes, migration/rollback/kill, owner/date/review trigger | Human architecture/security/ops approval |

An `adopt` ADR creates separately scoped implementation work; a `defer` ADR
proves no runtime/config component was added. Freeze exact metrics/threshold/
workload/approvers in `E5-DEC-007`, `E5-DEC-008`.
