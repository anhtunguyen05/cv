# Story 3.2: Preview a saved CV Version — Tasks

Every task is independently assignable after its dependencies and blockers are
satisfied. `todo` tasks have no owner or branch reservation yet.

## Tasks & Acceptance

**Execution:**

- [ ] TASK-3-2-01: Freeze CV Version Preview projection fixtures
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `none`
  - Covers: `AC-3-2-preview-a-saved-cv-version-01`, `AC-3-2-preview-a-saved-cv-version-02`, `AC-3-2-preview-a-saved-cv-version-03`, `AC-3-2-preview-a-saved-cv-version-04`
  - Scope: 01. Freeze CV snapshot render projection and fixtures: source tuple, supported sections, empty/long/unsafe/schema/error/accessibility/visual fixtures
  - Coordination: `E3-COORD-RENDER-001`, `E3-COORD-TEST-001`
  - Blocked by: `E3-PREREQ-VERSION-001`; `E3-DEC-001`; `E3-DEC-002`; `E3-DEC-003`; `E3-DEC-004`; `E3-DEC-006`; `E3-DEC-007`; approved E3-COORD-TEMPLATE-001 catalog checkpoint
  - Outcome: 01. Freeze CV snapshot render projection and fixtures: Freeze one render contract shared by API, Preview, and later Export.
  - Acceptance: 01. Freeze CV snapshot render projection and fixtures: fixtures identify exact Version/Template/renderer and expected semantic content/order.
  - Verification: 01. Freeze CV snapshot render projection and fixtures: schema/fixture validation and product/architecture/UX approval evidence.

- [ ] TASK-3-2-02: Deliver owned CV Version Preview projection
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-3-2-01`
  - Covers: `AC-3-2-preview-a-saved-cv-version-01`, `AC-3-2-preview-a-saved-cv-version-02`, `AC-3-2-preview-a-saved-cv-version-03`
  - Scope: 01. Implement owned CV Version Preview projection: query/application service, ownership, snapshot schema, Template compatibility, safe resource | 02. Verify source isolation, ownership, and renderer safety: two-User, Version A/Profile draft/Version B, schema, markup, URL, failure, no-mutation tests
  - Coordination: `E3-COORD-RENDER-001`, `E3-COORD-TEST-001`
  - Blocked by: `E3-DEC-007`
  - Outcome: 01. Implement owned CV Version Preview projection: Return an exact immutable, authorized, renderer-ready projection. | 02. Verify source isolation, ownership, and renderer safety: Prove Preview derives only from the authorized immutable source.
  - Acceptance: 01. Implement owned CV Version Preview projection: draft/current Profile data is never queried or merged and incompatible sources fail atomically. | 02. Verify source isolation, ownership, and renderer safety: exact synthetic markers prove no draft/foreign/version substitution across all layers.
  - Verification: 01. Implement owned CV Version Preview projection: unit/Laravel feature/contract tests with two Users and snapshot variants. | 02. Verify source isolation, ownership, and renderer safety: approved Laravel/Vitest/security commands with fixture IDs.

- [ ] TASK-3-2-03: Deliver and verify saved Version Preview
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-3-2-01`
  - Covers: `AC-3-2-preview-a-saved-cv-version-01`, `AC-3-2-preview-a-saved-cv-version-02`, `AC-3-2-preview-a-saved-cv-version-03`, `AC-3-2-preview-a-saved-cv-version-04`
  - Scope: 01. Implement exact-source Preview interaction: schema validation, tuple/cache key, direct route, loading/error/retry/stale state | 02. Build one semantic responsive CV renderer: section registry/order, empty omission, safe text/links/dates, approved Template layout, long/Unicode/overflow states | 03. Verify accessible saved-Version Preview: semantic outline, keyboard/focus, screen-reader names, visual corpus, and browser select/open/reload/draft-change/empty/unsafe/foreign/error scenarios
  - Coordination: `E3-COORD-RENDER-001`, `E3-COORD-TEST-001`
  - Blocked by: `E3-DEC-004`; `E3-DEC-006`; `E3-DEC-007`
  - Outcome: 01. Implement exact-source Preview interaction: Preserve the selected immutable source across navigation and reject invalid projections. | 02. Build one semantic responsive CV renderer: Render supported saved content through the shared screen/print boundary across approved Templates. | 03. Verify accessible saved-Version Preview: Prove semantic, keyboard, visual, and end-to-end behavior across approved extremes.
  - Acceptance: 01. Implement exact-source Preview interaction: refresh/back/multi-tab and all shared fixtures map deterministically without source switching. | 02. Build one semantic responsive CV renderer: no Template-local source inference, unsafe execution, orphan section, or silent required-content loss; empty, minimal, normal, and long fixtures preserve invariants. | 03. Verify accessible saved-Version Preview: semantic/content assertions and approved baselines pass, with exact source tuple and disposable synthetic Users; snapshots are not sole evidence. | Integrated browser journey acceptance closes only after `TASK-3-2-02` passes the owned-projection acceptance.
  - Verification: 01. Implement exact-source Preview interaction: type-check and adapter/router/query tests. | 02. Build one semantic responsive CV renderer: component/DOM/content-security/accessibility tests over the renderer corpus and approved visual baselines. | 03. Verify accessible saved-Version Preview: accessibility audit, keyboard/focus review, approved Playwright command, visual artifacts, and fixture/reset evidence. | Run the browser journey after `TASK-3-2-02` passes the owned-projection acceptance.

## Dependency and concurrency map
- `TASK-3-2-01` depends on `none`.
- `TASK-3-2-02` depends on `TASK-3-2-01`.
- `TASK-3-2-03` depends on `TASK-3-2-01`; its frontend or evidence work can proceed alongside `TASK-3-2-02`, and integrated acceptance closes after `TASK-3-2-02` is done.


Shared section-registry and renderer files remain reserved to one coordination owner.
