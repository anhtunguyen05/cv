# Story 3.3: Export a reviewed CV — Tasks

Every task is independently assignable after its dependencies and blockers are
satisfied. `todo` tasks have no owner or branch reservation yet.

- [ ] TASK-3-3-01: Freeze browser Export contract and print fixtures
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `none`
  - Covers: `AC-3-3-export-a-reviewed-cv-01` through `AC-3-3-export-a-reviewed-cv-03`
  - Scope: tuple/readiness, print page/style/content, capability/failure/cancel/race/retry/ownership fixtures
  - Coordination: `E3-COORD-RENDER-001`, `E3-COORD-PRINT-001`, `E3-COORD-TEST-001`
  - Blocked by: approved `E3-COORD-RENDER-001` renderer checkpoint from Story 3.2; `E3-DEC-003` through `E3-DEC-007`
  - Outcome: Freeze one verifiable browser Export boundary without server PDF scope.
  - Acceptance: fixtures distinguish ready, invoked, returned/unknown, failed, and unauthorized states.
  - Verification: contract/fixture validation and product/architecture/UX approval evidence.
- [ ] TASK-3-3-02: Implement exact reviewed-source Export state
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-3-3-01`
  - Covers: `AC-3-3-export-a-reviewed-cv-01`, `AC-3-3-export-a-reviewed-cv-02`
  - Scope: source tuple equality, readiness invalidation, preparation state, retry/cleanup, multi-tab/navigation races
  - Coordination: `E3-COORD-PRINT-001`
  - Blocked by: `E3-PREREQ-VERSION-001`
  - Outcome: Export only the currently reviewed immutable source and retain safe recovery context.
  - Acceptance: any source/version/Template change aborts stale preparation without duplicate state.
  - Verification: state/store/composable unit tests using shared fixtures.
- [ ] TASK-3-3-03: Build browser print surface and stylesheet
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-3-3-01`
  - Covers: `AC-3-3-export-a-reviewed-cv-01`, `AC-3-3-export-a-reviewed-cv-02`
  - Scope: shared renderer reuse, print route/container, page/margin/break/overflow/link rules, hidden controls
  - Coordination: `E3-COORD-RENDER-001`, `E3-COORD-PRINT-001`
  - Blocked by: `none`
  - Outcome: Prepare application-ready browser output without forking source content.
  - Acceptance: approved fixtures preserve required content/order and expose no UI/debug/session data.
  - Verification: print-emulation content/style tests and approved visual baselines.
- [ ] TASK-3-3-04: Implement Export control and browser invocation lifecycle
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-3-3-02`, `TASK-3-3-03`
  - Covers: `AC-3-3-export-a-reviewed-cv-01`, `AC-3-3-export-a-reviewed-cv-02`
  - Scope: explicit User action, readiness/assets, invoke wrapper, unsupported/blocked/error/return/retry feedback
  - Coordination: `E3-COORD-PRINT-001`
  - Blocked by: `none`
  - Outcome: Invoke print accessibly and honestly without unverifiable success copy.
  - Acceptance: repeated clicks are guarded, focus/context returns safely, and retry is deterministic.
  - Verification: component/browser-wrapper tests and keyboard/manual checks.
- [ ] TASK-3-3-05: Verify ownership and no-provider/no-worker boundary
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-3-3-02`
  - Covers: `AC-3-3-export-a-reviewed-cv-01`, `AC-3-3-export-a-reviewed-cv-03`
  - Scope: session/foreign/missing source, source content exposure, logs/network calls, dependency boundary
  - Coordination: `E3-COORD-TEST-001`
  - Blocked by: `E3-DEC-007`
  - Outcome: Prove only the owner can reach source content and Export requires no AI/worker call.
  - Acceptance: two-User and network assertions show no foreign artifact/data and no provider/worker dependency.
  - Verification: Laravel/browser/network security tests with synthetic data.
- [ ] TASK-3-3-06: Verify print layout, failures, and repeatability
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-3-3-03`, `TASK-3-3-04`
  - Covers: `AC-3-3-export-a-reviewed-cv-01`, `AC-3-3-export-a-reviewed-cv-02`
  - Scope: approved browser/page matrix, empty/long/Unicode/unsafe content, asset failure, cancel/return/retry
  - Coordination: `E3-COORD-PRINT-001`, `E3-COORD-TEST-001`
  - Blocked by: `E3-DEC-006`, `E3-DEC-007`
  - Outcome: Prove print output and recovery meet approved semantic and visual invariants.
  - Acceptance: repeated unchanged runs preserve content/order; failures never report a completed artifact.
  - Verification: print-emulation, content assertions, visual review, and failure injection evidence.
- [ ] TASK-3-3-07: Verify reviewed CV Export journey end to end
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-3-3-05`, `TASK-3-3-06`
  - Covers: `AC-3-3-export-a-reviewed-cv-01` through `AC-3-3-export-a-reviewed-cv-03`
  - Scope: browser Preview/export/source-race/failure/retry/foreign/keyboard/network scenarios
  - Coordination: `E3-COORD-TEST-001`
  - Blocked by: `E3-DEC-007`
  - Outcome: Verify the complete reviewed-source browser Export journey.
  - Acceptance: scenarios pass independently with exact tuples, synthetic Users, and no AI/worker traffic.
  - Verification: approved Playwright/print command and artifact/reset evidence.

## Dependency and concurrency map

```text
01 -> 02 ------\
  \-> 03 -> 04 +-> 06 --\
       02 --------> 05 ----> 07
```

Tasks 02 and 03 may proceed in parallel after fixtures. Shared renderer and
print files remain serialized through their coordination owners.
