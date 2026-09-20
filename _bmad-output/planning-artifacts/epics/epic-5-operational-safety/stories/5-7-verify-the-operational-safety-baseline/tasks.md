# Story 5.7 — Tasks

## Tasks & Acceptance

**Execution:**

- [ ] TASK-5-7-01: Freeze scope, evidence, and readiness fixtures
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `none`
  - Covers: `AC-5-7-verify-the-operational-safety-baseline-01`
  - Scope: 01. Freeze committed scope, evidence manifest, verdict, freshness, and waiver fixtures: included/excluded controls, source/evidence/gap/ADR/verdict/supersession schema and required matrix
  - Coordination: `E5-COORD-BASELINE-001`, `E5-COORD-TEST-001`
  - Blocked by: `E5-DEC-008`; `E5-DEC-009`; committed Story owners identified
  - Outcome: 01. Freeze committed scope, evidence manifest, verdict, freshness, and waiver fixtures: One objective fail-closed operational verdict contract.
  - Acceptance: 01. Freeze committed scope, evidence manifest, verdict, freshness, and waiver fixtures: Missing, failed, stale, inaccessible, or leaking evidence cannot produce a passing verdict.
  - Verification: 01. Freeze committed scope, evidence manifest, verdict, freshness, and waiver fixtures: architecture/security/ops/product/release review evidence.

- [ ] TASK-5-7-02: Implement operational evidence manifest validator
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-5-7-01`
  - Covers: `AC-5-7-verify-the-operational-safety-baseline-01`
  - Scope: 01. Implement read-only evidence manifest validator: schema/source/version/result/integrity/freshness/access/canary/gap/waiver/ADR checks and exit codes
  - Coordination: `E5-COORD-BASELINE-001`
  - Blocked by: none
  - Outcome: 01. Implement read-only evidence manifest validator: Reject incomplete or unsafe evidence without changing Story artifacts.
  - Acceptance: 01. Implement read-only evidence manifest validator: seeded omission/failure/stale/tamper/canary/expired waiver cases fail deterministically.
  - Verification: 01. Implement read-only evidence manifest validator: unit/property/golden/negative tests.

- [ ] TASK-5-7-03: Assemble evidence and verify operational readiness
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-5-7-01`
  - Covers: `AC-5-7-verify-the-operational-safety-baseline-01`
  - Scope: 01. Assemble the immutable evidence manifest and reconcile committed scope: reference Story-owner artifacts and versions, exclusions, unresolved decisions, gaps, owners, risks, actions, and waiver expiry without copying source evidence | 02. Validate evidence and run approved cross-control scenarios: use the manifest validator and approved provider/Patch, privacy-canary, deletion, telemetry/runbook, match-regression, and ADR scenarios; retain failures and reconcile gaps | 03. Facilitate the independent human readiness review and prove the verdict: record scoped pass/pass-with-gaps/fail, release/kill implications, approvals/date, independent rerun, privacy/integrity, and supersession chain
  - Coordination: `E5-COORD-BASELINE-001`, `E5-COORD-TEST-001`; one readiness integrator owns manifest assembly and verdict evidence; source-story evidence owners provide artifacts, while approvers and the independent rerun reviewer must be separate from that integrator and from evidence authors
  - Blocked by: `E5-DEC-008`; `E5-DEC-009`; accepted evidence checkpoints from all committed Stories 5.1–5.6
  - Outcome: 01. Assemble the immutable evidence manifest and reconcile committed scope: Produce a complete reference to Story-owned evidence, exclusions, and unresolved work. | 02. Validate evidence and run approved cross-control scenarios: Prove committed controls compose safely and preserve any failed result. | 03. Facilitate the independent human readiness review and prove the verdict: Record one accountable operational decision with reproducible, private, immutable support.
  - Acceptance: 01. Assemble the immutable evidence manifest and reconcile committed scope: each committed control maps to owner/result/artifact/version/time; exclusions and unresolved decisions are explicit. | 02. Validate evidence and run approved cross-control scenarios: scenarios use exact source versions; required failures remain visible and each gap/waiver has an owner, risk, action, dependency, and expiry. | 03. Facilitate the independent human readiness review and prove the verdict: approvers resolve findings and accept/reject waivers; reruns are consistent, canaries absent, and prior manifests remain immutable. | Final manifest validation, reproducibility, and task closure require `TASK-5-7-02` acceptance; readiness approvers cannot be the evidence owner.
  - Verification: 01. Assemble the immutable evidence manifest and reconcile committed scope: manifest schema/link/access/integrity/freshness and independent gap-to-source reconciliation. | 02. Validate evidence and run approved cross-control scenarios: approved staging-like/disposable commands, immutable artifacts, and retained failure/gap/waiver evidence. | 03. Facilitate the independent human readiness review and prove the verdict: dated independent approvals, validator transcript, rerun/privacy/integrity scans, and supersession evidence. | Evidence collection and scenario preparation can proceed after `TASK-5-7-01`; validator-backed final verification and closure wait until `TASK-5-7-02` passes acceptance.

## Dependency and concurrency map
- `TASK-5-7-01` depends on `none`.
- `TASK-5-7-02` depends on `TASK-5-7-01`.
- `TASK-5-7-03` starts after `TASK-5-7-01`; manifest assembly and scenario preparation can proceed while `TASK-5-7-02` is built, but validator-backed final verification and task closure require `TASK-5-7-02` acceptance.
