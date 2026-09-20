# Story 3.3: Export a reviewed CV — Tasks

Every task is independently assignable after its dependencies and blockers are
satisfied. `todo` tasks have no owner or branch reservation yet.

## Tasks & Acceptance

**Execution:**

- [ ] TASK-3-3-01: Freeze browser Export contract and print fixtures
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `none`
  - Covers: `AC-3-3-export-a-reviewed-cv-01`, `AC-3-3-export-a-reviewed-cv-02`, `AC-3-3-export-a-reviewed-cv-03`
  - Scope: 01. Freeze browser Export contract and print fixtures: tuple/readiness, print page/style/content, capability/failure/cancel/race/retry/ownership fixtures
  - Coordination: `E3-COORD-RENDER-001`, `E3-COORD-PRINT-001`, `E3-COORD-TEST-001`
  - Blocked by: `E3-DEC-003`; `E3-DEC-004`; `E3-DEC-005`; `E3-DEC-006`; `E3-DEC-007`; approved E3-COORD-RENDER-001 renderer checkpoint from Story 3.2
  - Outcome: 01. Freeze browser Export contract and print fixtures: Freeze one verifiable browser Export boundary without server PDF scope.
  - Acceptance: 01. Freeze browser Export contract and print fixtures: fixtures distinguish ready, invoked, returned/unknown, failed, and unauthorized states.
  - Verification: 01. Freeze browser Export contract and print fixtures: contract/fixture validation and product/architecture/UX approval evidence.

- [ ] TASK-3-3-02: Deliver the reviewed-source browser Export boundary
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-3-3-01`
  - Covers: `AC-3-3-export-a-reviewed-cv-01`, `AC-3-3-export-a-reviewed-cv-02`, `AC-3-3-export-a-reviewed-cv-03`
  - Scope: 01. Implement exact reviewed-source Export state: source tuple equality, readiness invalidation, preparation state, retry/cleanup, multi-tab/navigation races | 02. Verify ownership and no-provider/no-worker boundary: session/foreign/missing source, source content exposure, logs/network calls, dependency boundary
  - Coordination: `E3-COORD-PRINT-001`, `E3-COORD-TEST-001`
  - Blocked by: `E3-PREREQ-VERSION-001`; `E3-DEC-007`
  - Outcome: 01. Implement exact reviewed-source Export state: Export only the currently reviewed immutable source and retain safe recovery context. | 02. Verify ownership and no-provider/no-worker boundary: Prove only the owner can reach source content and Export requires no AI/worker call.
  - Acceptance: 01. Implement exact reviewed-source Export state: any source/version/Template change aborts stale preparation without duplicate state. | 02. Verify ownership and no-provider/no-worker boundary: two-User and network assertions show no foreign artifact/data and no provider/worker dependency.
  - Verification: 01. Implement exact reviewed-source Export state: state/store/composable unit tests using shared fixtures. | 02. Verify ownership and no-provider/no-worker boundary: Laravel/browser/network security tests with synthetic data.

- [ ] TASK-3-3-03: Deliver and verify reviewed CV Export
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-3-3-01`
  - Covers: `AC-3-3-export-a-reviewed-cv-01`, `AC-3-3-export-a-reviewed-cv-02`, `AC-3-3-export-a-reviewed-cv-03`
  - Scope: 01. Build browser print surface and stylesheet: shared renderer reuse, print route/container, page/margin/break/overflow/link rules, hidden controls | 02. Implement Export control and browser invocation lifecycle: explicit User action, readiness/assets, invoke wrapper, unsupported/blocked/error/return/retry feedback | 03. Verify print layout, failures, and repeatability: approved browser/page matrix, empty/long/Unicode/unsafe content, asset failure, cancel/return/retry | 04. Verify reviewed CV Export journey end to end: browser Preview/export/source-race/failure/retry/foreign/keyboard/network scenarios
  - Coordination: `E3-COORD-RENDER-001`, `E3-COORD-PRINT-001`, `E3-COORD-TEST-001`
  - Blocked by: `E3-DEC-006`; `E3-DEC-007`
  - Outcome: 01. Build browser print surface and stylesheet: Prepare application-ready browser output without forking source content. | 02. Implement Export control and browser invocation lifecycle: Invoke print accessibly and honestly without unverifiable success copy. | 03. Verify print layout, failures, and repeatability: Prove print output and recovery meet approved semantic and visual invariants. | 04. Verify reviewed CV Export journey end to end: Verify the complete reviewed-source browser Export journey.
  - Acceptance: 01. Build browser print surface and stylesheet: approved fixtures preserve required content/order and expose no UI/debug/session data. | 02. Implement Export control and browser invocation lifecycle: repeated clicks are guarded, focus/context returns safely, and retry is deterministic. | 03. Verify print layout, failures, and repeatability: repeated unchanged runs preserve content/order; failures never report a completed artifact. | 04. Verify reviewed CV Export journey end to end: scenarios pass independently with exact tuples, synthetic Users, and no AI/worker traffic. | Integrated browser journey acceptance closes only after `TASK-3-3-02` proves the exact reviewed-source state against the approved Story 3.2 renderer checkpoint.
  - Verification: 01. Build browser print surface and stylesheet: print-emulation content/style tests and approved visual baselines. | 02. Implement Export control and browser invocation lifecycle: component/browser-wrapper tests and keyboard/manual checks. | 03. Verify print layout, failures, and repeatability: print-emulation, content assertions, visual review, and failure injection evidence. | 04. Verify reviewed CV Export journey end to end: approved Playwright/print command and artifact/reset evidence. | Run the browser journey after `TASK-3-3-02` passes acceptance against the approved Story 3.2 renderer checkpoint.

## Dependency and concurrency map
- `TASK-3-3-01` depends on `none`.
- `TASK-3-3-02` depends on `TASK-3-3-01`.
- `TASK-3-3-03` depends on `TASK-3-3-01`; its frontend or evidence work can proceed alongside `TASK-3-3-02`, and integrated acceptance closes after `TASK-3-3-02` is done.


Shared renderer and print files remain serialized through their coordination owners.
