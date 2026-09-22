# Story 4.5: Approve a Patch into a new CV Version — Tasks

## Tasks & Acceptance

**Execution:**

- [ ] TASK-4-5-01: Freeze Patch apply and result fixtures
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `none`
  - Covers: `AC-4-5-approve-a-patch-into-a-new-cv-version-01`, `AC-4-5-approve-a-patch-into-a-new-cv-version-02`, `AC-4-5-approve-a-patch-into-a-new-cv-version-03`
  - Scope: 01. Freeze apply transaction, stale, race, and result fixtures: confirmation/revalidation/transform/name/provenance/idempotency/conflict/failure payloads and states
  - Coordination: `E4-COORD-PATCH-001`, `E4-COORD-APPLY-001`, `E4-COORD-TEST-001`
  - Blocked by: `E4-PREREQ-VERSION-001`; `E4-DEC-001`; `E4-DEC-004`; `E4-DEC-007`; `E4-DEC-008`; `E4-DEC-009`; approved E4-COORD-PATCH-001 review/status checkpoint from Story 4.4
  - Outcome: 01. Freeze apply transaction, stale, race, and result fixtures: Freeze one atomic Patch-to-Version contract and race matrix.
  - Acceptance: 01. Freeze apply transaction, stale, race, and result fixtures: fixtures identify every write boundary and prove source immutability.
  - Verification: 01. Freeze apply transaction, stale, race, and result fixtures: schema/fixture and product/architecture/security/UX approval evidence.

- [ ] TASK-4-5-02: Deliver atomic Patch application backend
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-4-5-01`
  - Covers: `AC-4-5-approve-a-patch-into-a-new-cv-version-01`, `AC-4-5-approve-a-patch-into-a-new-cv-version-02`, `AC-4-5-approve-a-patch-into-a-new-cv-version-03`
  - Scope: 01. Implement pure Patch transform and full snapshot revalidation: allowlisted target resolution, exact old value, typed new value, Evidence/status/source, complete CV schema | 02. Implement atomic idempotent Patch application service: owner/status locks, Version/provenance insert, Patch applied/result transition, dedupe, rollback, competing decisions | 03. Expose explicit Patch approval/reconciliation API: Form Request, policy, controller/resource/route, confirmation/precondition/idempotency, error/result mapping | 04. Verify apply atomicity, ownership, idempotency, and races: PostgreSQL write failures, two-User/mixed source, stale old value, approve/reject races, lost result, provenance
  - Coordination: `E4-COORD-PATCH-001`, `E4-COORD-APPLY-001`, `E4-COORD-TEST-001`
  - Blocked by: `E4-DEC-009`
  - Outcome: 01. Implement pure Patch transform and full snapshot revalidation: Produce a validated in-memory result or a deterministic stale/invalid failure. | 02. Implement atomic idempotent Patch application service: Commit exactly one result Version and applied Patch together. | 03. Expose explicit Patch approval/reconciliation API: Serve a non-disclosing, conflict-aware, reconcilable approval contract. | 04. Verify apply atomicity, ownership, idempotency, and races: Prove exactly-once consistent application and immutable history.
  - Acceptance: 01. Implement pure Patch transform and full snapshot revalidation: transform cannot mutate source input or touch an unapproved field/item. | 02. Implement atomic idempotent Patch application service: injected failure at every write rolls back all changes; repeat returns same result. | 03. Expose explicit Patch approval/reconciliation API: success/stale/race/retry/lost/foreign/malformed cases match fixtures. | 04. Verify apply atomicity, ownership, idempotency, and races: No state may show a Patch as applied without a Version, or a Version without applied status and provenance.
  - Verification: 01. Implement pure Patch transform and full snapshot revalidation: unit/property/golden snapshot and adversarial tests. | 02. Implement atomic idempotent Patch application service: application/PostgreSQL transaction, constraint, concurrency, and fault-injection tests. | 03. Expose explicit Patch approval/reconciliation API: Laravel feature/contract tests with two Users and forced failures. | 04. Verify apply atomicity, ownership, idempotency, and races: approved PHPUnit/PostgreSQL/Vitest commands and database evidence.

- [ ] TASK-4-5-03: Deliver and verify Patch approval journey
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-4-5-01`
  - Covers: `AC-4-5-approve-a-patch-into-a-new-cv-version-01`, `AC-4-5-approve-a-patch-into-a-new-cv-version-02`, `AC-4-5-approve-a-patch-into-a-new-cv-version-03`
  - Scope: 01. Build accessible approval, conflict, retry, and result UI: adapter/mutation, explicit confirmation, pending/disabled/stale/failure/reconcile/success states, focus/result link | 02. Verify explicit Patch approval journey end to end: browser confirm/success/stale/failure/retry/lost/race/foreign/keyboard and source/result comparison
  - Coordination: `E4-COORD-APPLY-001`, `E4-COORD-TEST-001`
  - Blocked by: `E4-DEC-009`
  - Outcome: 01. Build accessible approval, conflict, retry, and result UI: Keep the User informed and in control without duplicate approval. | 02. Verify explicit Patch approval journey end to end: Verify complete human-approved Patch-to-Version behavior.
  - Acceptance: 01. Build accessible approval, conflict, retry, and result UI: repeated click guarded, stale distinct from retryable, and success identifies source/result clearly. | 02. Verify explicit Patch approval journey end to end: disposable scenarios prove one result Version, unchanged source, and accessible recovery. | Integrated journey acceptance closes only after `TASK-4-5-02` is done with backend evidence.
  - Verification: 01. Build accessible approval, conflict, retry, and result UI: type-check, adapter/component, keyboard, and accessibility tests. | 02. Verify explicit Patch approval journey end to end: approved Playwright command and fixture/reset evidence. | Run the cross-layer journey check after `TASK-4-5-02` passes its backend acceptance.

## Dependency and concurrency map
- `TASK-4-5-01` depends on `none`.
- `TASK-4-5-02` depends on `TASK-4-5-01`.
- `TASK-4-5-03` depends on `TASK-4-5-01`; its frontend or evidence work can proceed alongside `TASK-4-5-02`, and integrated acceptance closes after `TASK-4-5-02` is done.


Reserve application transaction files to one `E4-COORD-APPLY-001` owner; keep the frontend scope separate and use the approved fixtures.
