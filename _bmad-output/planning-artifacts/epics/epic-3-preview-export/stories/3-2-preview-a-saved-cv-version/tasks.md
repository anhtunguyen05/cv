# Story 3.2: Preview a saved CV Version — Tasks

Every task is independently assignable after its dependencies and blockers are
satisfied. `todo` tasks have no owner or branch reservation yet.

- [ ] TASK-3-2-01: Freeze CV snapshot render projection and fixtures
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `none`
  - Covers: `AC-3-2-preview-a-saved-cv-version-01` through `AC-3-2-preview-a-saved-cv-version-04`
  - Scope: source tuple, supported sections, empty/long/unsafe/schema/error/accessibility/visual fixtures
  - Coordination: `E3-COORD-RENDER-001`, `E3-COORD-TEST-001`
  - Blocked by: `E3-PREREQ-VERSION-001`; approved `E3-COORD-TEMPLATE-001` catalog checkpoint; `E3-DEC-001` through `E3-DEC-004`, `E3-DEC-006`, `E3-DEC-007`
  - Outcome: Freeze one render contract shared by API, Preview, and later Export.
  - Acceptance: fixtures identify exact Version/Template/renderer and expected semantic content/order.
  - Verification: schema/fixture validation and product/architecture/UX approval evidence.
- [ ] TASK-3-2-02: Implement owned CV Version Preview projection
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-3-2-01`
  - Covers: `AC-3-2-preview-a-saved-cv-version-01` through `AC-3-2-preview-a-saved-cv-version-03`
  - Scope: query/application service, ownership, snapshot schema, Template compatibility, safe resource
  - Coordination: `E3-COORD-RENDER-001`
  - Blocked by: `none`
  - Outcome: Return an exact immutable, authorized, renderer-ready projection.
  - Acceptance: draft/current Profile data is never queried or merged and incompatible sources fail atomically.
  - Verification: unit/Laravel feature/contract tests with two Users and snapshot variants.
- [ ] TASK-3-2-03: Implement Preview adapter, route, and query state
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-3-2-01`
  - Covers: `AC-3-2-preview-a-saved-cv-version-01` through `AC-3-2-preview-a-saved-cv-version-04`
  - Scope: schema validation, exact tuple/cache key, direct route, loading/error/retry/stale state
  - Coordination: `E3-COORD-RENDER-001`
  - Blocked by: `none`
  - Outcome: Preserve source identity across navigation and reject invalid projection before rendering.
  - Acceptance: refresh/back/multi-tab and every shared fixture map deterministically without source switching.
  - Verification: type-check and adapter/router/query tests.
- [ ] TASK-3-2-04: Build shared semantic CV section renderer
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-3-2-03`
  - Covers: `AC-3-2-preview-a-saved-cv-version-01` through `AC-3-2-preview-a-saved-cv-version-04`
  - Scope: section registry, canonical order, empty omission, safe text/links/dates, semantic document structure
  - Coordination: `E3-COORD-RENDER-001`
  - Blocked by: `none`
  - Outcome: Render all supported saved content through one reusable screen/print content boundary.
  - Acceptance: no Template-local source inference, unsafe execution, orphan section, or silent required-content loss.
  - Verification: component/DOM/content-security/accessibility tests over renderer corpus.
- [ ] TASK-3-2-05: Apply responsive Template Preview presentation
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-3-2-04`
  - Covers: `AC-3-2-preview-a-saved-cv-version-01`, `AC-3-2-preview-a-saved-cv-version-03`, `AC-3-2-preview-a-saved-cv-version-04`
  - Scope: approved Template layout, overflow, long/Unicode content, controls, screen states, focus
  - Coordination: `E3-COORD-RENDER-001`
  - Blocked by: `E3-DEC-004`, `E3-DEC-006`
  - Outcome: Present an understandable application-ready Preview across approved viewports.
  - Acceptance: visual and semantic invariants hold for empty, minimal, normal, long, and failure fixtures.
  - Verification: component, accessibility, responsive, and approved visual-baseline review.
- [ ] TASK-3-2-06: Verify source isolation, ownership, and renderer safety
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-3-2-02`, `TASK-3-2-04`
  - Covers: `AC-3-2-preview-a-saved-cv-version-01` through `AC-3-2-preview-a-saved-cv-version-03`
  - Scope: two-User, Version A/Profile draft/Version B, schema, markup, URL, failure, no-mutation tests
  - Coordination: `E3-COORD-TEST-001`
  - Blocked by: `E3-DEC-007`
  - Outcome: Prove Preview derives only from the authorized immutable source.
  - Acceptance: exact synthetic markers prove no draft/foreign/version substitution across all layers.
  - Verification: approved Laravel/Vitest/security commands with fixture IDs.
- [ ] TASK-3-2-07: Verify Preview accessibility and visual acceptance
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-3-2-05`
  - Covers: `AC-3-2-preview-a-saved-cv-version-01`, `AC-3-2-preview-a-saved-cv-version-03`, `AC-3-2-preview-a-saved-cv-version-04`
  - Scope: semantic outline, keyboard/focus, screen-reader names, viewports, overflow, visual corpus
  - Coordination: `E3-COORD-TEST-001`
  - Blocked by: `E3-DEC-006`, `E3-DEC-007`
  - Outcome: Prove Preview remains usable and visually coherent under approved extremes.
  - Acceptance: semantic/content assertions and approved baselines pass; snapshots are not sole evidence.
  - Verification: accessibility audit, component tests, and visual review evidence.
- [ ] TASK-3-2-08: Verify saved Version Preview journey end to end
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-3-2-06`, `TASK-3-2-07`
  - Covers: `AC-3-2-preview-a-saved-cv-version-01` through `AC-3-2-preview-a-saved-cv-version-04`
  - Scope: browser select/open/reload/draft-change/empty/unsafe/foreign/error/keyboard scenarios
  - Coordination: `E3-COORD-TEST-001`
  - Blocked by: `E3-DEC-007`
  - Outcome: Verify the complete exact-snapshot Preview journey.
  - Acceptance: scenarios pass independently with exact source tuple and disposable synthetic Users.
  - Verification: approved Playwright command, visual artifacts, and fixture/reset evidence.

## Dependency and concurrency map

```text
01 -> 02 -----------------> 06 --\
  \-> 03 -> 04 -> 05 -> 07 ------> 08
            \---------> 06
```

Tasks 02 and 03 may proceed in parallel after fixtures. Shared section registry
and renderer files remain reserved to one coordination owner.
