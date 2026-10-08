# Story 5.5 — Tasks

## Tasks & Acceptance

**Execution:**

- [ ] TASK-5-5-01: Adopt the approved Epic 2 corpus as an immutable Epic 5 input
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `none`
  - Covers: `AC-5-5-validate-deterministic-matching-quality-01`
  - Scope: Create `docs/contracts/jd/fixtures/match-quality-evaluation-v1.json` as a manifest of the exact `DISCOVERY-E2-001` fixture path, corpus hash, 40 reviewed plus 12 held-out case inventory, and matcher/analysis/report schema versions; do not alter the source corpus in this task.
  - Coordination: `E5-COORD-QUALITY-001`
  - Blocked by: `E5-DEC-008`; named quality owner and implementation worktree.
  - Outcome: One pinned, synthetic, read-only evaluator input manifest.
  - Acceptance: The manifest identifies every input version and rejects an unavailable, changed, or non-synthetic corpus before evaluation.
  - Verification: Corpus hash/version fixture test and quality-owner review evidence.

- [ ] TASK-5-5-02: Freeze evaluation metrics, thresholds, and waiver semantics
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-5-5-01`
  - Covers: `AC-5-5-validate-deterministic-matching-quality-01`
  - Scope: Define the versioned classification, score, ordering, repeatability, performance, and unsupported-claim counter-metrics; define thresholds, tolerances, diagnostic fields, verdicts, and a time-bounded waiver shape.
  - Coordination: `E5-COORD-QUALITY-001`, `E5-COORD-TEST-001`
  - Blocked by: named quality owner and implementation worktree.
  - Outcome: One approved pass/fail evaluation contract independent of runner code.
  - Acceptance: A fixture can be evaluated on paper to one verdict; an aggregate pass cannot hide an unsupported-claim or repeatability counter-metric failure.
  - Verification: Worked metric examples, negative examples, and dated quality/security approval evidence.

- [ ] TASK-5-5-03: Implement the read-only evaluator boundary
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-5-5-01`, `TASK-5-5-02`
  - Covers: `AC-5-5-validate-deterministic-matching-quality-01`
  - Scope: Add `apps/api/app/Application/JobFit/MatchQualityEvaluator.php`, an Artisan command under `apps/api/app/Console/Commands/`, and focused tests under `apps/api/tests/Feature/JobFit/`; validate fixture/version compatibility, invoke the deterministic matcher in memory, canonicalize output, and emit diagnostics plus an aggregate verdict without a persistence write path.
  - Coordination: `E5-COORD-QUALITY-001`
  - Blocked by: `E5-DEC-008`; approved Epic 2 matcher consumer checkpoint and completion of `TASK-5-5-02`.
  - Outcome: A deterministic evaluator callable with only synthetic fixture input.
  - Acceptance: Invalid versions/fixtures fail before matching; compatible inputs produce only the contract result and cannot create or update a User, Match Report, or other product record.
  - Verification: Unit, golden, malformed-input, version-mismatch, and database write-trap tests.

- [ ] TASK-5-5-04: Add repeatability and regression verdict computation
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-5-5-03`
  - Covers: `AC-5-5-validate-deterministic-matching-quality-01`
  - Scope: Execute the frozen run count and shuffled-order repeats, compare canonical output, compute the approved metrics/counter-metrics, and return stable exit classes for pass, quality regression, repeatability failure, invalid input, and infrastructure failure.
  - Coordination: `E5-COORD-QUALITY-001`, `E5-COORD-TEST-001`
  - Blocked by: completion of `TASK-5-5-03`.
  - Outcome: Reproducible evaluation verdicts with actionable safe diagnostics.
  - Acceptance: Seeded classification, score, ordering, counter-metric, and repeatability regressions fail with their distinct approved verdict; clean repetitions pass identically.
  - Verification: Repeated-process, shuffled-order, seeded-regression, exit-code, and performance-budget tests.

- [ ] TASK-5-5-05: Publish an immutable safe evaluation artifact
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-5-5-04`
  - Covers: `AC-5-5-validate-deterministic-matching-quality-01`
  - Scope: Wire the evaluator into `.github/workflows/ci-api.yml`, define its artifact schema/naming/version pins/access-retention classification/failure behavior, and publish only synthetic safe diagnostics; never overwrite an accepted baseline implicitly.
  - Coordination: `E5-COORD-QUALITY-001`, `E5-COORD-TEST-001`
  - Blocked by: `E5-DEC-008`; completion of `TASK-5-5-04`; and CI artifact access confirmation.
  - Outcome: One reviewable artifact for each evaluator run.
  - Acceptance: A failed threshold produces a non-zero command outcome and retained failing artifact; the artifact exposes no User content, credentials, or mutable product state.
  - Verification: Clean, regression, infrastructure-failure, artifact-schema, access-classification, and prohibited-content canary checks.

- [ ] TASK-5-5-06: Define controlled baseline-change and regression triage
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-5-5-02`
  - Covers: `AC-5-5-validate-deterministic-matching-quality-01`
  - Scope: Document diagnosis, owner, separate corpus/rule/threshold versioning, reviewer approval, waiver expiry, rollback, and rerun requirements for an evaluation failure or intended baseline change.
  - Coordination: `E5-COORD-QUALITY-001`
  - Blocked by: named quality reviewer.
  - Outcome: A review workflow that prevents silent expected-result or threshold laundering.
  - Acceptance: Every allowed baseline change requires a reason, immutable evidence, non-author review, explicit version, and rollback path; expired waivers fail closed.
  - Verification: Seeded-regression tabletop walkthrough and reviewer sign-off.

- [ ] TASK-5-5-07: Verify the end-to-end deterministic quality gate
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-5-5-05`, `TASK-5-5-06`
  - Covers: `AC-5-5-validate-deterministic-matching-quality-01`
  - Scope: Run the approved clean, repeated, version-mismatch, seeded-regression, waiver, write-trap, and artifact checks from the pinned source commit in the approved disposable environment.
  - Coordination: `E5-COORD-QUALITY-001`, `E5-COORD-TEST-001`
  - Blocked by: `E5-DEC-008`; named quality and environment owners.
  - Outcome: Immutable evidence that the quality gate is deterministic, read-only, and reviewable.
  - Acceptance: The evidence records exact inputs, command, environment, artifact references, verdicts, and no-mutation proof; all required negative cases fail as specified.
  - Verification: Independent rerun plus approved local/CI transcript and PostgreSQL write-trap evidence.

## Dependency and concurrency map

- `TASK-5-5-01` establishes the immutable input; `TASK-5-5-02` freezes the
  result contract after that input is pinned.
- `TASK-5-5-03` then `TASK-5-5-04` form the read-only evaluator path.
- `TASK-5-5-05` publishes the runner result, while `TASK-5-5-06` may proceed
  after the metric contract is approved.
- `TASK-5-5-07` is the sole integration closeout and waits for both artifact and
  triage work. All tasks remain `todo` until their named decision and ownership
  gates are approved.
