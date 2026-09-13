# Story 3.1: Select a Template — Tasks

Every task is independently assignable after its dependencies and blockers are
satisfied. `todo` tasks have no owner or branch reservation yet.

- [ ] TASK-3-1-01: Freeze Template catalog and selection fixtures
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `none`
  - Covers: `AC-3-1-select-a-template-01`, `AC-3-1-select-a-template-02`
  - Scope: active/inactive/unavailable/incompatible/malformed/empty catalog payloads and selection matrix
  - Coordination: `E3-COORD-TEMPLATE-001`, `E3-COORD-TEST-001`
  - Blocked by: `E3-DEC-001` through `E3-DEC-003`, `E3-DEC-006`, `E3-DEC-007`
  - Outcome: Freeze one catalog/selection contract shared by Laravel and Vue.
  - Acceptance: fixtures identify exact version, deterministic order, stale state, and no-side-effect behavior.
  - Verification: schema/fixture validation and product/architecture/UX approval evidence.
- [ ] TASK-3-1-02: Implement trusted Template registry and availability policy
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-3-1-01`
  - Covers: `AC-3-1-select-a-template-01`, `AC-3-1-select-a-template-02`
  - Scope: registry/config/model, identity/version, active/compatibility rules, deterministic query
  - Coordination: `E3-COORD-TEMPLATE-001`
  - Blocked by: `none`
  - Outcome: Resolve only trusted selectable Templates for a source context.
  - Acceptance: inactive, incompatible, duplicate, and malformed definitions fail safely and deterministically.
  - Verification: unit/config validation tests using canonical catalog fixtures.
- [ ] TASK-3-1-03: Expose authenticated Template catalog API
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-3-1-02`
  - Covers: `AC-3-1-select-a-template-01`, `AC-3-1-select-a-template-02`
  - Scope: query service, policy, resource/controller/route, envelope/error/cache mapping
  - Coordination: `E3-COORD-TEMPLATE-001`
  - Blocked by: `none`
  - Outcome: Serve safe ordered Template summaries without exposing executable registry details.
  - Acceptance: auth, empty, active, unavailable, malformed, and repeat requests match fixtures.
  - Verification: Laravel feature/contract tests.
- [ ] TASK-3-1-04: Implement Template catalog adapter and selection state
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-3-1-01`
  - Covers: `AC-3-1-select-a-template-01`, `AC-3-1-select-a-template-02`
  - Scope: response validation, query/cache key, exact selected tuple, stale/error mapping
  - Coordination: `E3-COORD-TEMPLATE-001`
  - Blocked by: `none`
  - Outcome: Hold deterministic catalog and selection state without hidden side effects.
  - Acceptance: every shared fixture maps to a typed loading/empty/error/selected/unavailable state.
  - Verification: type-check and adapter/store/query tests.
- [ ] TASK-3-1-05: Build accessible Template chooser
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-3-1-04`
  - Covers: `AC-3-1-select-a-template-01`, `AC-3-1-select-a-template-02`
  - Scope: cards/list, safe metadata, selection, empty/error/retry/unavailable states, focus behavior
  - Coordination: `E3-COORD-TEMPLATE-001`
  - Blocked by: `E3-DEC-006`
  - Outcome: Let keyboard and assistive-technology Users understand and select available Templates.
  - Acceptance: state and selection are perceivable without color, markup does not execute, and CV context survives reselection.
  - Verification: component tests and manual keyboard/screen-reader checklist.
- [ ] TASK-3-1-06: Verify Template catalog contract and safety
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-3-1-03`, `TASK-3-1-05`
  - Covers: `AC-3-1-select-a-template-01`, `AC-3-1-select-a-template-02`
  - Scope: cross-layer fixture, auth, ordering, incompatibility, escaping, cache, and accessibility suite
  - Coordination: `E3-COORD-TEST-001`
  - Blocked by: `E3-DEC-007`
  - Outcome: Prove catalog trust and selection behavior across API and UI.
  - Acceptance: backend and frontend consume the same fixture IDs/versions with no local variant.
  - Verification: approved Laravel/Vitest/accessibility commands and evidence.
- [ ] TASK-3-1-07: Verify Template selection journey end to end
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-3-1-06`
  - Covers: `AC-3-1-select-a-template-01`, `AC-3-1-select-a-template-02`
  - Scope: browser catalog/empty/select/refresh/stale/unavailable/keyboard scenarios
  - Coordination: `E3-COORD-TEST-001`
  - Blocked by: approved `E3-COORD-RENDER-001` Preview-entry checkpoint from Story 3.2; `E3-DEC-007`
  - Outcome: Verify selection hands one exact valid tuple to Preview and creates no side effect.
  - Acceptance: scenarios pass independently with synthetic data and deterministic catalog configuration.
  - Verification: approved Playwright command and fixture/reset evidence.

## Dependency and concurrency map

```text
01 -> 02 -> 03 --\
  \-> 04 -> 05 ----> 06 -> 07
```

Tasks 02 and 04 may proceed in parallel after fixtures when reserved files do
not overlap. The Story owner integrates catalog and selection contracts.
