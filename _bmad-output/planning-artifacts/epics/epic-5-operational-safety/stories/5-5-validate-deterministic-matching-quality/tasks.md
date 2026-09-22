# Story 5.5 — Tasks

## Tasks & Acceptance

**Execution:**

- [ ] TASK-5-5-01: Approve corpus and freeze quality metrics
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `none`
  - Covers: `AC-5-5-validate-deterministic-matching-quality-01`
  - Scope: 01. Curate and approve representative synthetic matching corpus: CV/JD/Analysis/expected report cases, aliases/negation/required/optional/Weak/missing/ties/Unicode/counterexamples | 02. Freeze evaluation metrics, thresholds, counter-metrics, and result fixtures: canonical hash/repeat runs, classification/score/order metrics, tolerances, diagnostics, exits, waiver/change
  - Coordination: `E5-COORD-QUALITY-001`, `E5-COORD-TEST-001`
  - Blocked by: `E5-PREREQ-MATCH-001`; `E5-DEC-006`; `DISCOVERY-E2-001`; `E5-DEC-008`
  - Outcome: 01. Curate and approve representative synthetic matching corpus: Human-approved versioned corpus that represents expected matcher usefulness. | 02. Freeze evaluation metrics, thresholds, counter-metrics, and result fixtures: One objective versioned pass/fail contract.
  - Acceptance: 01. Curate and approve representative synthetic matching corpus: each fixture has rationale/source versions/expected output and no production User content. | 02. Freeze evaluation metrics, thresholds, counter-metrics, and result fixtures: false-positive/unsupported-claim counter-metrics prevent a misleading aggregate pass.
  - Verification: 01. Curate and approve representative synthetic matching corpus: product/domain/quality/privacy review evidence. | 02. Freeze evaluation metrics, thresholds, counter-metrics, and result fixtures: metric worked examples and approval evidence.

- [ ] TASK-5-5-02: Deliver deterministic matching quality runner
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-5-5-01`
  - Covers: `AC-5-5-validate-deterministic-matching-quality-01`
  - Scope: 01. Implement read-only matcher evaluation runner: fixture/schema/version validation, in-memory matcher invocation, canonicalization/repetition/metrics/diagnostics/exits | 02. Integrate evaluation command and immutable safe artifact: local/CI entry, pinned versions, artifact schema/name/access/retention, failure exit and regression diff | 03. Verify repeatability, quality, performance, and no mutation: repeated runs, shuffled order, version changes, threshold/counter-metric failures, database write trap, performance budget
  - Coordination: `E5-COORD-QUALITY-001`, `E5-COORD-TEST-001`
  - Blocked by: `E5-DEC-008`; approved Epic 2 matcher consumer checkpoint
  - Outcome: 01. Implement read-only matcher evaluation runner: Produce reproducible quality evidence without persistence capability. | 02. Integrate evaluation command and immutable safe artifact: Make matching regression visible and reviewable. | 03. Verify repeatability, quality, performance, and no mutation: Prove deterministic useful evaluation and strict read-only behavior.
  - Acceptance: 01. Implement read-only matcher evaluation runner: runner rejects incompatible/prohibited input and cannot update reports/User data. | 02. Integrate evaluation command and immutable safe artifact: failing threshold fails the command and baseline cannot update implicitly. | 03. Verify repeatability, quality, performance, and no mutation: same inputs match exactly; seeded regressions fail; User/report state hash remains unchanged.
  - Verification: 01. Implement read-only matcher evaluation runner: unit/golden/property/mutation-guard tests. | 02. Integrate evaluation command and immutable safe artifact: clean/failing/infrastructure CI simulation and artifact validation. | 03. Verify repeatability, quality, performance, and no mutation: approved evaluator/PostgreSQL write-trap/performance command evidence.

- [ ] TASK-5-5-03: Document quality triage and close the runner acceptance
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-5-5-01`
  - Covers: `AC-5-5-validate-deterministic-matching-quality-01`
  - Scope: 01. Document regression triage and controlled baseline-change workflow: owner, diagnostic review, matcher/rule/corpus/threshold version changes, approval, waiver/expiry, rollback | 02. Verify deterministic quality gate end to end: clean run/repeat/seed regression/version mismatch/waiver/no-mutation/artifact scenarios
  - Coordination: `E5-COORD-QUALITY-001`, `E5-COORD-TEST-001`
  - Blocked by: `E5-DEC-008`
  - Outcome: 01. Document regression triage and controlled baseline-change workflow: Prevent silent expected-output or threshold laundering. | 02. Verify deterministic quality gate end to end: Verify the MVP matching quality baseline and review workflow.
  - Acceptance: 01. Document regression triage and controlled baseline-change workflow: every baseline change requires reason, separate version, reviewer, evidence and rollback. | 02. Verify deterministic quality gate end to end: independent runs produce safe exact artifacts and expected exit/verdict. | Final quality-gate acceptance closes only after `TASK-5-5-02` passes runner acceptance.
  - Verification: 01. Document regression triage and controlled baseline-change workflow: dry-run seeded regression walkthrough and reviewer sign-off. | 02. Verify deterministic quality gate end to end: approved local/CI command transcript and immutable artifact references. | Run the independent local/CI quality-gate check after `TASK-5-5-02` passes runner acceptance.

## Dependency and concurrency map
- `TASK-5-5-01` depends on `none`.
- `TASK-5-5-02` depends on `TASK-5-5-01`.
- `TASK-5-5-03` depends on `TASK-5-5-01`; documentation and independent review/evidence can proceed alongside `TASK-5-5-02`, while final runner acceptance waits for its output.
