# Story 5.6 — Tasks

## Tasks & Acceptance

**Execution:**

- [ ] TASK-5-6-01: Freeze orchestration workload and metric baseline
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `none`
  - Covers: `AC-5-6-decide-whether-multi-agent-orchestration-is-justified-01`, `AC-5-6-decide-whether-multi-agent-orchestration-is-justified-02`
  - Scope: 01. Freeze representative workloads, baseline, metrics, and limitation threshold: scenarios/versions/config/environment/repetitions/quality/latency/cost/reliability/operability/variance/threshold
  - Coordination: `E5-COORD-ADR-001`, `E5-COORD-TEST-001`
  - Blocked by: `E5-PREREQ-AI-001`; `E5-DEC-007`; `E5-DEC-008`
  - Outcome: 01. Freeze representative workloads, baseline, metrics, and limitation threshold: Pre-register a reproducible unbiased measurement plan.
  - Acceptance: 01. Freeze representative workloads, baseline, metrics, and limitation threshold: thresholds and analysis are frozen before observing results.
  - Verification: 01. Freeze representative workloads, baseline, metrics, and limitation threshold: architecture/product/security/ops review evidence.

- [ ] TASK-5-6-02: Measure workloads and evaluate orchestration options
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-5-6-01`
  - Covers: `AC-5-6-decide-whether-multi-agent-orchestration-is-justified-01`, `AC-5-6-decide-whether-multi-agent-orchestration-is-justified-02`
  - Scope: 01. Build isolated single-orchestrator measurement harness: workload runner, pinned config/provider fake/sandbox, safe measurements, repetitions/artifact schema | 02. Execute baseline measurement and publish immutable safe results: approved runs, failures/variance/raw safe data, aggregate, artifact references, limitation classification | 03. Analyze alternatives and preserved safety boundaries: tuning/deterministic split/queue/multi-agent options, ownership/tools/writes/validation/human control/cost/ops/failure
  - Coordination: `E5-COORD-ADR-001`, `E5-COORD-TEST-001`
  - Blocked by: none
  - Outcome: 01. Build isolated single-orchestrator measurement harness: Measure the existing boundary without adding agent/runtime components. | 02. Execute baseline measurement and publish immutable safe results: Produce decision-grade evidence for the current orchestrator. | 03. Analyze alternatives and preserved safety boundaries: Compare the least-complex remedies under equal evidence.
  - Acceptance: 01. Build isolated single-orchestrator measurement harness: harness reproduces plan and contains no production User content/secrets. | 02. Execute baseline measurement and publish immutable safe results: all planned runs and failures are included; artifact pins versions/environment. | 03. Analyze alternatives and preserved safety boundaries: any multi-agent proposal has bounded responsibilities, no broader authority, migration/rollback/kill.
  - Verification: 01. Build isolated single-orchestrator measurement harness: harness contract/repeatability/privacy tests. | 02. Execute baseline measurement and publish immutable safe results: independent rerun/sample reconciliation and artifact validation. | 03. Analyze alternatives and preserved safety boundaries: architecture/security/operations threat and tradeoff review.

- [ ] TASK-5-6-03: Record independent ADR approval and verify reproducibility
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-5-6-02`
  - Covers: `AC-5-6-decide-whether-multi-agent-orchestration-is-justified-01`, `AC-5-6-decide-whether-multi-agent-orchestration-is-justified-02`
  - Scope: 01. Draft adopt-or-defer orchestration ADR: evidence/decision/rationale/consequences/invariants/owner/date/trigger/migration/rollback and follow-up scope | 02. Review and approve the orchestration decision: architecture/security/product/ops independent review, objections, evidence gaps, final status/date/trigger | 03. Verify decision reproducibility and no unauthorized component: rerun evidence, ADR completeness, code/config/dependency diff audit, trigger and separate follow-up checks
  - Coordination: `E5-COORD-ADR-001`, `E5-COORD-TEST-001`; one ADR coordinator owns the decision record and evidence handoffs; reviewers and the independent rerun verifier must not be the ADR author
  - Blocked by: `E5-DEC-008`
  - Outcome: 01. Draft the adopt-or-defer orchestration ADR: Record a reviewable planning decision without implementing it. | 02. Obtain independent approval and resolve objections: Produce an evidence-backed verdict signed by reviewers other than the ADR author. | 03. Verify decision reproducibility and no unauthorized component: Prove the verdict follows completed measurements and no runtime/configuration component was added.
  - Acceptance: 01. Draft the adopt-or-defer orchestration ADR: insufficient evidence requires a defer verdict and no runtime or configuration change. | 02. Obtain independent approval and resolve objections: architecture/security/product/ops reviewers confirm metrics, alternatives, safety invariants, and follow-up authority boundary; the author cannot approve their own ADR. | 03. Verify decision reproducibility and no unauthorized component: independent rerun is materially consistent; defer has zero component changes and adopt creates separate approved scope. | ADR acceptance closes only after `TASK-5-6-02` provides the frozen measurement results.
  - Verification: 01. Draft the adopt-or-defer orchestration ADR: ADR schema/link/preservation and planning-only diff-boundary review. | 02. Obtain independent approval and resolve objections: dated approvals and resolved findings from non-author reviewers. | 03. Verify decision reproducibility and no unauthorized component: independent rerun, repository diff audit, and ADR validation evidence after measurement acceptance.

## Dependency and concurrency map
- `TASK-5-6-01` depends on `none`.
- `TASK-5-6-02` depends on `TASK-5-6-01`.
- `TASK-5-6-03` depends on `TASK-5-6-02`; ADR drafting, independent approval, and reproducibility verification proceed from its completed measurement evidence.
