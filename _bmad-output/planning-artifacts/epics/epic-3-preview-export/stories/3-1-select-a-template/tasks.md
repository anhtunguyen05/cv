# Story 3.1: Select a Template — Tasks

Every task is independently assignable after its dependencies and blockers are
satisfied. `todo` tasks have no owner or branch reservation yet.

## Tasks & Acceptance

**Execution:**

- [ ] TASK-3-1-01: Freeze Template catalog and selection fixtures
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `none`
  - Covers: `AC-3-1-select-a-template-01`, `AC-3-1-select-a-template-02`
  - Scope: 01. Freeze Template catalog and selection fixtures: active/inactive/unavailable/incompatible/malformed/empty catalog payloads and selection matrix
  - Coordination: `E3-COORD-TEMPLATE-001`, `E3-COORD-TEST-001`
  - Blocked by: `E3-DEC-001`; `E3-DEC-002`; `E3-DEC-003`; `E3-DEC-006`; `E3-DEC-007`
  - Outcome: 01. Freeze Template catalog and selection fixtures: Freeze one catalog/selection contract shared by Laravel and Vue.
  - Acceptance: 01. Freeze Template catalog and selection fixtures: fixtures identify exact version, deterministic order, stale state, and no-side-effect behavior.
  - Verification: 01. Freeze Template catalog and selection fixtures: schema/fixture validation and product/architecture/UX approval evidence.

- [ ] TASK-3-1-02: Deliver trusted Template catalog backend
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-3-1-01`
  - Covers: `AC-3-1-select-a-template-01`, `AC-3-1-select-a-template-02`
  - Scope: 01. Implement trusted Template registry and availability policy: registry/config/model, identity/version, active/compatibility rules, deterministic query | 02. Expose authenticated Template catalog API: query service, policy, resource/controller/route, envelope/error/cache mapping | 03. Verify Template catalog contract and safety: API fixture, auth, ordering, incompatibility, escaping, and cache suite
  - Coordination: `E3-COORD-TEMPLATE-001`, `E3-COORD-TEST-001`
  - Blocked by: `E3-DEC-007`
  - Outcome: 01. Implement trusted Template registry and availability policy: Resolve only trusted selectable Templates for a source context. | 02. Expose authenticated Template catalog API: Serve safe ordered Template summaries without exposing executable registry details. | 03. Verify Template catalog contract and safety: Prove catalog trust and selection behavior across API and UI.
  - Acceptance: 01. Implement trusted Template registry and availability policy: inactive, incompatible, duplicate, and malformed definitions fail safely and deterministically. | 02. Expose authenticated Template catalog API: auth, empty, active, unavailable, malformed, and repeat requests match fixtures. | 03. Verify Template catalog contract and safety: backend and frontend consume the same fixture IDs/versions with no local variant.
  - Verification: 01. Implement trusted Template registry and availability policy: unit/config validation tests using canonical catalog fixtures. | 02. Expose authenticated Template catalog API: Laravel feature/contract tests. | 03. Verify Template catalog contract and safety: approved Laravel/API fixture-parity and escaping command evidence.

- [ ] TASK-3-1-03: Deliver and verify Template selection journey
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-3-1-01`
  - Covers: `AC-3-1-select-a-template-01`, `AC-3-1-select-a-template-02`
  - Scope: 01. Implement Template catalog adapter and selection state: response validation, query/cache key, exact selected tuple, stale/error mapping | 02. Build accessible Template chooser: cards/list, safe metadata, selection, empty/error/retry/unavailable states, focus behavior | 03. Verify Template selection journey end to end: browser catalog/empty/select/refresh/stale/unavailable/keyboard scenarios
  - Coordination: `E3-COORD-TEMPLATE-001`, `E3-COORD-TEST-001`
  - Blocked by: `E3-DEC-006`; `E3-DEC-007`; approved E3-COORD-RENDER-001 Preview-entry checkpoint from Story 3.2
  - Outcome: 01. Implement Template catalog adapter and selection state: Hold deterministic catalog and selection state without hidden side effects. | 02. Build accessible Template chooser: Let keyboard and assistive-technology Users understand and select available Templates. | 03. Verify Template selection journey end to end: Verify selection hands one exact valid tuple to Preview and creates no side effect.
  - Acceptance: 01. Implement Template catalog adapter and selection state: every shared fixture maps to a typed loading/empty/error/selected/unavailable state. | 02. Build accessible Template chooser: state and selection are perceivable without color, markup does not execute, and CV context survives reselection. | 03. Verify Template selection journey end to end: scenarios pass independently with synthetic data and deterministic catalog configuration. | Integrated journey acceptance closes only after `TASK-3-1-02` is done with backend evidence.
  - Verification: 01. Implement Template catalog adapter and selection state: type-check and adapter/store/query tests. | 02. Build accessible Template chooser: component tests and manual keyboard/screen-reader checklist. | 03. Verify Template selection journey end to end: approved Playwright command and fixture/reset evidence. | Run the cross-layer journey check after `TASK-3-1-02` passes its backend acceptance.

## Dependency and concurrency map
- `TASK-3-1-01` depends on `none`.
- `TASK-3-1-02` depends on `TASK-3-1-01`.
- `TASK-3-1-03` depends on `TASK-3-1-01`; its frontend or evidence work can proceed alongside `TASK-3-1-02`, and integrated acceptance closes after `TASK-3-1-02` is done.


The Story owner integrates the catalog and selection contracts.
