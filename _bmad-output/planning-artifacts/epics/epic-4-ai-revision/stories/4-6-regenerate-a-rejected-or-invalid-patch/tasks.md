# Story 4.6: Regenerate a rejected or invalid Patch — Tasks

## Tasks & Acceptance

**Execution:**

- [ ] TASK-4-6-01: Freeze Patch regeneration fixtures
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `none`
  - Covers: `AC-4-6-regenerate-a-rejected-or-invalid-patch-01`, `AC-4-6-regenerate-a-rejected-or-invalid-patch-02`
  - Scope: 01. Freeze regeneration eligibility, lineage, stale, and failure fixtures: predecessor/context/feedback/provider/result/lineage/dedupe/conflict/next-action payloads
  - Coordination: `E4-COORD-INTERVIEW-001`, `E4-COORD-PROVIDER-001`, `E4-COORD-PATCH-001`, `E4-COORD-TEST-001`
  - Blocked by: `E4-DEC-001`; `E4-DEC-002`; `E4-DEC-003`; `E4-DEC-004`; `E4-DEC-005`; `E4-DEC-006`; `E4-DEC-007`; `E4-DEC-008`; `E4-DEC-009`; `DISCOVERY-E4-001`; `DISCOVERY-E4-002`; approved Interview/Evidence provider Patch review checkpoints from Stories 4.2–4.4
  - Outcome: 01. Freeze regeneration eligibility, lineage, stale, and failure fixtures: Freeze one safe lineage-preserving regeneration contract.
  - Acceptance: 01. Freeze regeneration eligibility, lineage, stale, and failure fixtures: fixtures distinguish eligible, stale, unavailable, retryable, terminal, and reconciled outcomes.
  - Verification: 01. Freeze regeneration eligibility, lineage, stale, and failure fixtures: schema/fixture and product/architecture/security/UX approval evidence.

- [ ] TASK-4-6-02: Deliver context-safe Patch regeneration backend
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-4-6-01`
  - Covers: `AC-4-6-regenerate-a-rejected-or-invalid-patch-01`, `AC-4-6-regenerate-a-rejected-or-invalid-patch-02`
  - Scope: 01. Implement regeneration eligibility and context validator: owner/status, source Version/old value, Interview/questions/Evidence availability, target, allowed feedback | 02. Implement idempotent lineage generation service: provider adapter/validator reuse, predecessor key, lease/dedupe, late/lost/failure, new Patch persistence transaction | 03. Expose regeneration and reconciliation API: Form Request, policy, controller/resource/route, feedback/precondition/idempotency, status/error/next-action mapping | 04. Verify lineage, provider safety, isolation, and races: two-User, predecessor/source immutability, stale contexts, minimum disclosure, injection, repeat/concurrent/late/lost outcomes
  - Coordination: `E4-COORD-INTERVIEW-001`, `E4-COORD-PATCH-001`, `E4-COORD-PROVIDER-001`, `E4-COORD-TEST-001`
  - Blocked by: `E4-PREREQ-OPS-001`; `E4-DEC-008`; `E4-DEC-009`; `DISCOVERY-E4-002`
  - Outcome: 01. Implement regeneration eligibility and context validator: Permit generation only from a complete still-valid historical context. | 02. Implement idempotent lineage generation service: Produce at most one validated new lineage result without rewriting history. | 03. Expose regeneration and reconciliation API: Serve a non-disclosing reconcilable regeneration contract. | 04. Verify lineage, provider safety, isolation, and races: Prove regeneration is safe, grounded, and history-preserving.
  - Acceptance: 01. Implement regeneration eligibility and context validator: stale/missing/negative/foreign/changed states fail before provider disclosure. | 02. Implement idempotent lineage generation service: predecessor/source never mutate and invalid/provider failure creates no pending Patch. | 03. Expose regeneration and reconciliation API: eligible/stale/status/provider/invalid/lost/foreign cases match fixtures. | 04. Verify lineage, provider safety, isolation, and races: one lineage result at most and no stale/foreign context reaches provider or pending state.
  - Verification: 01. Implement regeneration eligibility and context validator: domain/application/adversarial unit tests. | 02. Implement idempotent lineage generation service: provider contract, application/MySQL concurrency/rollback/idempotency tests. | 03. Expose regeneration and reconciliation API: Laravel feature/contract tests with two Users and provider fake. | 04. Verify lineage, provider safety, isolation, and races: approved provider/security/MySQL/evaluation/Vitest commands and evidence.

- [ ] TASK-4-6-03: Deliver and verify Patch regeneration journey
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-4-6-01`
  - Covers: `AC-4-6-regenerate-a-rejected-or-invalid-patch-01`, `AC-4-6-regenerate-a-rejected-or-invalid-patch-02`
  - Scope: 01. Build accessible regeneration and stale-context UI: adapter/mutation, predecessor reason/context, optional bounded feedback, confirm/progress/stale/failure/retry/new link/focus | 02. Verify Patch regeneration journey end to end: browser rejected/invalid/new-pending/stale/unavailable/provider-failure/retry/foreign/keyboard scenarios
  - Coordination: `E4-COORD-PATCH-001`, `E4-COORD-TEST-001`
  - Blocked by: `E4-DEC-009`
  - Outcome: 01. Build accessible regeneration and stale-context UI: Explain why regeneration is or is not possible while preserving prior decisions. | 02. Verify Patch regeneration journey end to end: Verify the complete historical Patch regeneration loop.
  - Acceptance: 01. Build accessible regeneration and stale-context UI: controls follow allowed actions, repeated click guarded, and next action is accessible/actionable. | 02. Verify Patch regeneration journey end to end: disposable scenarios preserve predecessor/source and navigate only to a separately identified valid proposal. | Integrated journey acceptance closes only after `TASK-4-6-02` is done with backend evidence.
  - Verification: 01. Build accessible regeneration and stale-context UI: type-check, adapter/component, keyboard, and accessibility tests. | 02. Verify Patch regeneration journey end to end: approved Playwright/provider-fake command and fixture/reset evidence. | Run the cross-layer journey check after `TASK-4-6-02` passes its backend acceptance.

## Dependency and concurrency map
- `TASK-4-6-01` depends on `none`.
- `TASK-4-6-02` depends on `TASK-4-6-01`.
- `TASK-4-6-03` depends on `TASK-4-6-01`; its frontend or evidence work can proceed alongside `TASK-4-6-02`, and integrated acceptance closes after `TASK-4-6-02` is done.


Keep provider and Patch shared files under their single coordination owners; preserve the approved upstream eligibility and source-context gates.
