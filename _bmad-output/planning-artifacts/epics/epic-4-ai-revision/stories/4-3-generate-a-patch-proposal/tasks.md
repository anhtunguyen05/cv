# Story 4.3: Generate a Patch proposal — Tasks

## Tasks & Acceptance

**Execution:**

- [ ] TASK-4-3-01: Freeze Patch generation and provider fixtures
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `none`
  - Covers: `AC-4-3-generate-a-patch-proposal-01`, `AC-4-3-generate-a-patch-proposal-02`
  - Scope: 01. Freeze provider, Patch, failure, and adversarial fixtures: eligible/minimum-data/result/schema/grounding/injection/retry/audit/pending payloads
  - Coordination: `E4-COORD-PROVIDER-001`, `E4-COORD-PATCH-001`, `E4-COORD-TEST-001`
  - Blocked by: accepted `E4-PREREQ-MATCH-001` and `E4-COORD-INTERVIEW-001` Evidence-context checkpoints; an assigned integration owner before task start
  - Outcome: 01. Freeze provider, Patch, failure, and adversarial fixtures: Freeze one provider-to-validated-Patch boundary and quality gate.
  - Acceptance: 01. Freeze provider, Patch, failure, and adversarial fixtures: fixtures prove only grounded allowlisted output can become pending.
  - Verification: 01. Freeze provider, Patch, failure, and adversarial fixtures: schema/adversarial/evaluation review and approval evidence.

- [ ] TASK-4-3-02: Deliver validated Patch generation backend
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-4-3-01`
  - Covers: `AC-4-3-generate-a-patch-proposal-01`, `AC-4-3-generate-a-patch-proposal-02`
  - Scope: 01. Implement strict Patch candidate validator: schema/version, allowlist, types/limits, old value/source, Evidence citations, safety, canonicalization | 02. Implement provider-neutral orchestrator contract and deterministic fake: provider interface, minimum-data mapper, prompt/tool versions, fake, correlation/redaction contract | 03. Implement synchronous generation service and pending Patch persistence: 15-second deadline, ownership/eligibility, dedupe/lease, fake call, validation, Patch model/migration, sanitized outcome transaction | 04. Expose Patch generation and read APIs: Form Request, policy, generation/read controller/resource/routes, idempotency, status/error mapping | 05. Verify fake-provider security and Patch quality gate: adversarial/quality/minimum-disclosure/logs/ownership/idempotency/source-mutation suite
  - Coordination: `E4-COORD-PATCH-001`, `E4-COORD-PROVIDER-001`, `E4-COORD-TEST-001`; one backend integration owner sequences validator, provider-neutral fake contract, generation/persistence, API, then the complete quality gate
  - Blocked by: accepted `E4-PREREQ-MATCH-001`, completed Story 4.2 Evidence checkpoint, and an assigned integration owner before task start; `E4-PREREQ-OPS-001` remains a production-activation gate only
  - Outcome: 01. Implement strict Patch candidate validator: Reject every unsupported or ungrounded candidate deterministically. | 02. Implement provider-neutral orchestrator contract and fake: Define one provider-independent untrusted DTO boundary testable without real traffic. | 03. Implement synchronous generation service and pending Patch persistence: Persist exactly one valid pending Patch or no misleading Patch. | 04. Expose Patch generation and read APIs: Serve safe generation/reconciliation and validated Patch read contracts. | 05. Verify fake-provider security and Patch quality gate: Prove the proposal is grounded, bounded, private, and never auto-applied.
  - Acceptance: 01. Implement strict Patch candidate validator: adversarial fixtures fail closed with stable reasons and no mutation. | 02. Implement provider-neutral orchestrator contract and fake: fake proves minimum input, no persistence authority, version pinning, and deterministic failure injection. | 03. Implement synchronous generation service and pending Patch persistence: source remains unchanged and concurrency/failure/rollback produce consistent state inside the 15-second deadline. | 04. Expose Patch generation and read APIs: eligible/success/provider/invalid/conflict/retry/lost/foreign cases match fixtures without raw provider errors or secrets. | 05. Verify fake-provider security and Patch quality gate: unsupported-claim counterexamples create no pending Patch.
  - Verification: 01. Implement strict Patch candidate validator: domain/property/adversarial unit tests. | 02. Implement provider-neutral orchestrator contract and fake: provider contract/fake/minimum-data/redaction unit tests. | 03. Implement synchronous generation service and pending Patch persistence: application/Laravel/PostgreSQL concurrency and rollback tests. | 04. Expose Patch generation and read APIs: Laravel feature/contract tests with two Users and provider fake. | 05. Verify fake-provider security and Patch quality gate: synthetic evaluation plus security/PostgreSQL command evidence.

- [ ] TASK-4-3-03: Deliver and verify accessible Patch generation journey
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-4-3-01`
  - Covers: `AC-4-3-generate-a-patch-proposal-01`, `AC-4-3-generate-a-patch-proposal-02`
  - Scope: 01. Build accessible frontend states for Patch generation: schema adapter, mutation/reconciliation, generate/progress/cancel/failure/retry/success states, and focus handling | 02. Verify Patch generation journey end to end: browser generate/success/invalid/timeout/retry/lost/foreign/keyboard/provider-fake scenarios
  - Coordination: `E4-COORD-PROVIDER-001`, `E4-COORD-PATCH-001`, `E4-COORD-TEST-001`
  - Blocked by: `E4-DEC-009`; approved E4-COORD-PATCH-001 review-entry checkpoint from Story 4.4
  - Outcome: 01. Implement accessible Patch generation frontend state: Present honest generation outcomes without client-created Patch truth. | 02. Verify Patch generation journey end to end: Verify complete Evidence-to-pending-Patch behavior.
  - Acceptance: 01. Implement accessible Patch generation frontend state: every fixture maps to an accessible state and no raw provider error/secret reaches UI. | 02. Verify Patch generation journey end to end: disposable scenarios expose only validated proposal data and keep source markers unchanged. | Integrated journey acceptance closes only after `TASK-4-3-02` is done with backend evidence.
  - Verification: 01. Implement accessible Patch generation frontend state: type-check, adapter/query/component/keyboard/accessibility tests. | 02. Verify Patch generation journey end to end: approved Playwright/provider-fake command and reset evidence. | Run the cross-layer journey check after `TASK-4-3-02` passes its backend acceptance.

## Dependency and concurrency map
- `TASK-4-3-01` depends on `none`.
- `TASK-4-3-02` depends on `TASK-4-3-01`.
- `TASK-4-3-03` depends on `TASK-4-3-01`; its frontend or evidence work can proceed alongside `TASK-4-3-02`, and integrated acceptance closes after `TASK-4-3-02` is done.


The backend integration owner hands the untrusted candidate through the validator
and provider-neutral fake contract before generation/persistence and API layers.
Frontend work can proceed after fixtures; real provider traffic remains blocked
by operational approval.
