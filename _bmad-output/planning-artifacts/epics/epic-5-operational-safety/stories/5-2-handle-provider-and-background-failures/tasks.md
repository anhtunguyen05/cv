# Story 5.2 — Tasks

## Tasks & Acceptance

**Execution:**

- [ ] TASK-5-2-01: Freeze provider failure and async job fixtures
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `none`
  - Covers: `AC-5-2-handle-provider-and-background-failures-01`, `AC-5-2-handle-provider-and-background-failures-02`
  - Scope: 01. Freeze provider failure and conditional job lifecycle fixtures: taxonomy/retry/job/lease/cancel/late/lost/result/status/audit/metric/User-state matrix
  - Coordination: `E5-COORD-AUDIT-001`, `E5-COORD-JOB-001`, `E5-COORD-TELEMETRY-001`, `E5-COORD-TEST-001`
  - Blocked by: `E5-PREREQ-AI-001`; `E5-DEC-002`; `E5-DEC-003`; `E5-DEC-005`; `E5-DEC-008`
  - Outcome: 01. Freeze provider failure and conditional job lifecycle fixtures: One approved failure/lifecycle contract without implicit queue scope.
  - Acceptance: 01. Freeze provider failure and conditional job lifecycle fixtures: every state/category has retry/terminal/allowed action and trusted-write rule. Async-job fixtures are included only when the async prerequisite and discovery are approved.
  - Verification: 01. Freeze provider failure and conditional job lifecycle fixtures: architecture/product/ops/security schema review evidence.

- [ ] TASK-5-2-02: Deliver and verify the provider failure recovery journey
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-5-2-01`
  - Covers: `AC-5-2-handle-provider-and-background-failures-01`
  - Scope: 01. Implement provider failure policy and bounded retry: timeout/rate/transport/malformed categories, safe retry/backoff/budget/idempotency/cancel rules and terminal errors | 02. Apply provider outcomes through the API and privacy-safe operations signals: caller integration, validated result boundary, allowed actions, safe audit/metrics, correlation and runbook hooks | 03. Deliver the accessible provider recovery experience: actionable retry/failure states and browser journey across timeout/rate/malformed/retry/lost/foreign cases
  - Coordination: `E5-COORD-AUDIT-001`, `E5-COORD-TELEMETRY-001`, `E5-COORD-TEST-001`
  - Blocked by: `E5-DEC-008`; approved audit and telemetry checkpoints from Stories 5.1/5.4 gate only their signal-integration clauses
  - Outcome: 01. Implement provider failure policy and bounded retry: Produce bounded, repeatable outcomes across provider callers. | 02. Apply provider outcomes through the API and privacy-safe operations signals: Map failures consistently without trusting malformed output or logging User content. | 03. Deliver the accessible provider recovery experience: Prove an actionable, truthful provider-failure journey with no partial trusted state.
  - Acceptance: 01. Implement provider failure policy and bounded retry: malformed terminal output is never retried into trusted state and retry budgets/cancellation are bounded. | 02. Apply provider outcomes through the API and privacy-safe operations signals: timeout/rate/transport/malformed/retry/lost cases create no unvalidated Patch or partial trusted state, and safe signals correlate without User content. | 03. Deliver the accessible provider recovery experience: keyboard-usable errors explain allowed actions without exposing provider internals, and disposable end-to-end scenarios pass with zero forbidden canaries.
  - Verification: 01. Implement provider failure policy and bounded retry: unit/property/provider-fake/fake-clock tests. | 02. Apply provider outcomes through the API and privacy-safe operations signals: Laravel/provider-fake/PostgreSQL rollback, audit/metric canary and cardinality tests. | 03. Deliver the accessible provider recovery experience: adapter/component/fake-clock/keyboard/accessibility tests and approved Playwright/provider-fake/PostgreSQL journey evidence.

- [ ] TASK-5-2-03: Deliver and verify the approved async job failure lifecycle
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-5-2-01`
  - Covers: `AC-5-2-handle-provider-and-background-failures-02`
  - Scope: 01. Implement the named async operation lifecycle and persistence conditionally: one approved job model, lease/attempt/deadline/status/result/idempotency rules, no generic queue | 02. Expose atomic result/status/retry/cancel behavior with audit, telemetry, and runbook correlation: only validated named-operation results can succeed | 03. Deliver accessible async status/recovery and verify crash/lease/retry/cancel/duplicate/late/lost/foreign journeys end to end
  - Coordination: `E5-COORD-JOB-001`, `E5-COORD-AUDIT-001`, `E5-COORD-TELEMETRY-001`, `E5-COORD-TEST-001`
  - Blocked by: approved audit and telemetry checkpoints from Stories 5.1/5.4; async implementation requires `E5-PREREQ-ASYNC-001` and `DISCOVERY-E5-004`; without an approved consumer, leave this task blocked and AC2 open
  - Outcome: 01. Implement the named async operation lifecycle and persistence conditionally: Persist one honest, versioned job lifecycle only for an approved consumer. | 02. Expose atomic result/status/recovery behavior with safe operations signals: Commit one validated result and expose truthful status/actions. | 03. Deliver accessible async status/recovery and verify the failure lifecycle: Prove one terminal truth and no false success across the approved journey.
  - Acceptance: 01. Implement the named async operation lifecycle and persistence conditionally: lease expiry, retries, duplicate and late attempts cannot create multiple results or false success. | 02. Expose atomic result/status/recovery behavior with safe operations signals: invalid results never commit and failure signals contain no User content. | 03. Deliver accessible async status/recovery and verify the failure lifecycle: approved disposable job scenarios pass; without an approved consumer, this task remains blocked and AC2 is not marked complete.
  - Verification: 01. Implement the named async operation lifecycle and persistence conditionally: PostgreSQL constraint/lease/duplicate/late/cancel and worker-fake fault tests. | 02. Expose atomic result/status/recovery behavior with safe operations signals: Laravel/PostgreSQL concurrency/rollback, audit/metric canary/cardinality tests. | 03. Deliver accessible async status/recovery and verify the failure lifecycle: adapter/component/keyboard/accessibility tests plus approved Playwright/worker-fake/PostgreSQL evidence.

## Dependency and concurrency map
- `TASK-5-2-01` depends on `none`.
- `TASK-5-2-02` depends on `TASK-5-2-01`; retry-policy and provider outcome work can proceed after fixtures, while audit/telemetry wiring waits for its approved checkpoints.
- `TASK-5-2-03` depends on `TASK-5-2-01`; named async lifecycle work can proceed after async approval, while audit/telemetry wiring waits for its approved checkpoints. AC1 and AC2 close only with their own evidence; AC2 stays open while async is unapproved.
