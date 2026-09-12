# Epic 3 Decisions and Coordination

Recommendations are planning proposals, not approved requirements. Before a
dependent Story becomes ready for development, every applicable decision needs
one owner, an approved resolution, and evidence in the form
`approver, YYYY-MM-DD`.

## Decision register

| ID | Status | Owner | Decision required | Recommended starting point | Resolution | Evidence | Blocks |
| --- | --- | --- | --- | --- | --- | --- | --- |
| E3-DEC-001 | `open` | `unassigned` | Template catalog and source API topology | Freeze endpoints, envelopes, status/error/cache behavior, catalog visibility, selection payload, and direct-route semantics | `pending` | `pending` | 3.1–3.3 |
| E3-DEC-002 | `open` | `unassigned` | Template registry and versioning | Freeze stable IDs, version rule, active/available/incompatible states, metadata, publication process, and safe renderer lookup | `pending` | `pending` | 3.1–3.3 |
| E3-DEC-003 | `open` | `unassigned` | Selection and Preview navigation state | Prefer URL-addressable `cv_version_id` plus Template identity with no trusted Preview persistence; freeze refresh/back/multi-tab/stale-selection behavior | `pending` | `pending` | 3.1, 3.2 |
| E3-DEC-004 | `open` | `unassigned` | CV section registry and renderer contract | Freeze supported fields/sections, ordering, omissions, dates, links, rich text, overflow, locale, schema compatibility, and renderer version | `pending` | `pending` | 3.2, 3.3 |
| E3-DEC-005 | `open` | `unassigned` | Browser Export contract | Freeze preparation/invocation/cancel semantics, page/margin/title rules, unsupported browser behavior, retry, and whether intent-only audit exists | `pending` | `pending` | 3.3 |
| E3-DEC-006 | `open` | `unassigned` | Accessibility and visual acceptance matrix | Freeze browsers, viewports, assistive checks, print-emulation method, tolerances, synthetic fixtures, and approval owner | `pending` | `pending` | 3.1–3.3 verification |
| E3-DEC-007 | `open` | `unassigned` | Shared harness and CI commands | Assign owners for fixtures, visual baselines, browser configuration, disposable User data, artifact retention, and flake policy | `pending` | `pending` | all verification tasks |

## Cross-Epic prerequisite

### E3-PREREQ-VERSION-001 — Immutable CV Version render source

- Source: Epic 1 `E1-COORD-VERSION-001` and `E1-CONTRACT-VERSION-001`.
- Required checkpoint: approved complete immutable snapshot schema, owned detail
  operation, source naming, and backward-compatible reader policy.
- Blocks: every Preview and Export implementation task.

## Cross-Story coordination records

### E3-COORD-TEMPLATE-001 — Template catalog and version registry

- Stories: 3.1 owner; 3.2 and 3.3 consumers.
- Decision owner: `unassigned`.
- Resolution: `pending E3-DEC-001, E3-DEC-002, E3-DEC-003`.
- Reserved boundary: Template registry/config/model, catalog endpoint/resource,
  selection schema/state, metadata UI, and catalog fixtures.
- Sequence/merge rule: Story 3.1 freezes and lands the catalog checkpoint;
  consumers reference the exact identity/version without redefining it.

### E3-COORD-RENDER-001 — Shared Preview/Export renderer

- Stories: 3.2 owner; 3.3 consumer.
- Decision owner: `unassigned`.
- Resolution: `pending E3-DEC-003, E3-DEC-004, E3-DEC-006`.
- Reserved boundary: source adapter, section registry, renderer components,
  semantic structure, shared screen/print content, and renderer fixtures.
- Sequence/merge rule: Story 3.2 freezes the deterministic render checkpoint;
  Story 3.3 adds print presentation without forking content mapping.

### E3-COORD-PRINT-001 — Browser print surface

- Stories: 3.3, with 3.2 as reviewed-source provider.
- Decision owner: `unassigned`.
- Resolution: `pending E3-DEC-005, E3-DEC-006, E3-DEC-007`.
- Reserved boundary: print route/state, stylesheet, browser invocation wrapper,
  title/filename hints, print-emulation baselines, and retry copy.
- Sequence/merge rule: source tuple and renderer checkpoint precede print work;
  one owner integrates shared CSS and browser baselines.

### E3-COORD-TEST-001 — Epic 3 verification

- Stories: all Epic 3 Stories.
- Decision owner: `unassigned`.
- Resolution: `pending E3-DEC-006, E3-DEC-007` and accepted Epic 1 harness.
- Reserved boundary: Template/CV fixtures, visual snapshots, print emulation,
  Playwright browser configuration, synthetic User data, and CI evidence.
- Sequence/merge rule: one test-integration owner lands shared corpus/harness
  changes; Story owners add bounded scenarios without parallel config edits.

## Discovered work outside current Story scope

### DISCOVERY-E3-001 — Server-generated downloadable artifact

- Status: `unassigned`.
- Owner: `unassigned`.
- Scope: define measurable browser-export gaps, PDF artifact requirements,
  storage/expiry/download contract, worker boundary, observability, and cost.
- Reason externalized: `AD-10` makes browser print/HTML the MVP; server PDF is
  not authorized without an approved artifact requirement.
