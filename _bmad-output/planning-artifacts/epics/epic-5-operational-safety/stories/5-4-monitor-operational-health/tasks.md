# Story 5.4 — Tasks

## Tasks & Acceptance

**Execution:**

- [ ] TASK-5-4-01: Inventory telemetry boundaries and freeze metric fixtures
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `none`
  - Covers: `AC-5-4-monitor-operational-health-01`
  - Scope: 01. Inventory supported operation boundaries and freeze metric fixtures: matching/provider/Patch/Export/job/system operation/status/failure/duration/version/label matrices
  - Coordination: `E5-COORD-AUDIT-001`, `E5-COORD-JOB-001`, `E5-COORD-TELEMETRY-001`, `E5-COORD-TEST-001`
  - Blocked by: `E5-DEC-001`; `E5-DEC-002`; `E5-DEC-003`; `E5-DEC-005`; `E5-DEC-008`; `DISCOVERY-E5-001`; `DISCOVERY-E5-002`; approved producer checkpoints
  - Outcome: 01. Inventory supported operation boundaries and freeze metric fixtures: One approved content-free versioned metric taxonomy.
  - Acceptance: 01. Inventory supported operation boundaries and freeze metric fixtures: every metric has boundary/unit/type/labels/cardinality/privacy/owner and no-data semantics.
  - Verification: 01. Inventory supported operation boundaries and freeze metric fixtures: architecture/ops/security/product review evidence.

- [ ] TASK-5-4-02: Deliver privacy-safe telemetry instrumentation
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-5-4-01`
  - Covers: `AC-5-4-monitor-operational-health-01`
  - Scope: 01. Implement telemetry schema, bounded labels, and privacy guard: typed metric helpers, allowlisted versions/categories, canary rejection, cardinality budget, outage behavior | 02. Instrument approved product and dependency lifecycle boundaries: supported matching/provider/Patch/Export/job/system success/failure/duration/retry emission | 03. Verify privacy, cardinality, metric correctness, and alert drills: canary scan, cardinality load, lifecycle counts/durations, outage/no-data, breach/dedupe/route/recovery/runbook
  - Coordination: `E5-COORD-TELEMETRY-001`, `E5-COORD-TEST-001`; one telemetry integration owner maintains the shared metric/privacy contract, coordinates producer-owner handoffs, and owns the complete telemetry evidence set
  - Blocked by: `E5-DEC-008`; only implemented approved operations are instrumented
  - Outcome: 01. Implement telemetry schema, bounded labels, and privacy guard: Make safe metric emission the reusable boundary. | 02. Instrument approved product and dependency lifecycle boundaries: Count exact lifecycle truth without duplicates or premature success. | 03. Verify privacy, cardinality, metric correctness, and alert drills: Prove signals are safe, correct, bounded, and actionable.
  - Acceptance: 01. Implement telemetry schema, bounded labels, and privacy guard: arbitrary/content labels cannot be emitted and telemetry failure cannot change product truth. | 02. Instrument approved product and dependency lifecycle boundaries: fake-clock/failure/retry/late/lost cases match metric fixtures. | 03. Verify privacy, cardinality, metric correctness, and alert drills: zero forbidden canaries, within budget, and drills reach owner/runbook with safe payload.
  - Verification: 01. Implement telemetry schema, bounded labels, and privacy guard: unit/property/fuzz/canary/cardinality tests. | 02. Instrument approved product and dependency lifecycle boundaries: integration and metric-sink tests per producer. | 03. Verify privacy, cardinality, metric correctness, and alert drills: approved load/privacy/integration/alert-drill artifact.

- [ ] TASK-5-4-03: Deliver authorized monitoring and verify alerts
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-5-4-01`
  - Covers: `AC-5-4-monitor-operational-health-01`
  - Scope: 01. Define and provision dashboards, SLOs, alerts, and runbooks: queries/windows/threshold/no-data, views, severity/routing/dedupe/recovery, owner/escalation/runbook | 02. Implement authorized accessible operational views: approved dashboard access, labels/units/windows/delay/no-data, drill links, keyboard/readability | 03. Verify operational health flow end to end: synthetic product outcomes-to-metric/dashboard/alert/ack/recovery/denial scenarios
  - Coordination: `E5-COORD-TELEMETRY-001`, `E5-COORD-TEST-001`; one monitoring integration owner coordinates dashboard/SLO/runbook configuration and authorized views, then the telemetry-backed drill and evidence
  - Blocked by: `E5-DEC-005`; `DISCOVERY-E5-002`; `E5-DEC-001`; `DISCOVERY-E5-001`; `E5-DEC-008`
  - Outcome: 01. Define and provision dashboards, SLOs, alerts, and runbooks: Turn safe telemetry into actionable operator response. | 02. Implement authorized accessible operational views: Let authorized operators understand health without User content. | 03. Verify operational health flow end to end: Verify complete privacy-safe detection and operator response.
  - Acceptance: 01. Define and provision dashboards, SLOs, alerts, and runbooks: each critical signal has owner/runbook and honest no-data/recovery behavior. | 02. Implement authorized accessible operational views: views distinguish no-data/zero and expose no forbidden labels or raw payload. | 03. Verify operational health flow end to end: versioned evidence traces every claim without User content. | Integrated monitoring acceptance closes only after `TASK-5-4-02` passes instrumentation and privacy-safe metric acceptance.
  - Verification: 01. Define and provision dashboards, SLOs, alerts, and runbooks: configuration validation and operator/runbook review evidence. | 02. Implement authorized accessible operational views: RBAC/dashboard/accessibility/config tests. | 03. Verify operational health flow end to end: approved staging-like drill and evidence manifest. | Run the monitoring flow after `TASK-5-4-02` passes instrumentation and privacy-safe metric acceptance.

## Dependency and concurrency map
- `TASK-5-4-01` depends on `none`.
- `TASK-5-4-02` depends on `TASK-5-4-01`.
- `TASK-5-4-03` depends on `TASK-5-4-01`; one monitoring integration owner is accountable for both operator-facing deliverables, which can proceed alongside `TASK-5-4-02`; integrated health-flow verification and closure wait for instrumentation acceptance.
