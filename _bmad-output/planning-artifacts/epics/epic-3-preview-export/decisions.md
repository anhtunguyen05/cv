# Epic 3 Decisions and Coordination

The resolutions below are the approved planning baseline for Epic 3. They are
accepted by Pc on 2026-10-07. Stable contract promotion and implementation
evidence remain separate delivery gates.

## Decision register

| ID | Status | Owner | Decision required | Resolution | Evidence | Blocks |
| --- | --- | --- | --- | --- | --- | --- |
| E3-DEC-001 | `approved` | `Pc` | Template catalog and source API topology | Authenticated `GET /api/v1/templates` returns `{data:[TemplateSummary]}` in deterministic `name ASC, id ASC` order. Authenticated `GET /api/v1/cv-versions/{cvVersion}/preview?template_id={ULID}&template_version={string}` resolves one owned immutable source tuple. Both use the common envelope and `Cache-Control: private, no-store`; foreign/missing Version is `404 CV_VERSION_NOT_FOUND`, while an inactive, stale, incompatible, or missing Template version is `409 TEMPLATE_UNAVAILABLE`. | Pc, 2026-10-07 | 3.1-3.3 |
| E3-DEC-002 | `approved` | `Pc` | Template registry and versioning | A Template is an application-owned `(template_id ULID, template_version)` pair. A published pair is immutable; any layout, mapping, or metadata behavior change publishes a new pair and never overwrites a reviewed pair. The registry permits only server-owned renderer keys and safe metadata, supports snapshot schema `1.0`, and exposes only active/available pairs. Existing `templates` schema must be reconciled before implementation so it can represent immutable published pairs rather than one mutable row per ID. | Pc, 2026-10-07 | 3.1-3.3 |
| E3-DEC-003 | `approved` | `Pc` | Selection and Preview navigation state | Selection is transient and URL-addressable: `/cv-versions/{cvVersionId}/templates` leads to `/cv-versions/{cvVersionId}/preview?template_id={ULID}&template_version={string}`. No Preview, Export, or active-template preference is persisted. Refresh, back/forward, and a second tab revalidate the exact tuple; a stale or unavailable pair returns to template selection while retaining the Version context. | Pc, 2026-10-07 | 3.1, 3.2 |
| E3-DEC-004 | `approved` | `Pc` | CV section registry and renderer contract | Renderer `1.0.0` supports snapshot schema `1.0`: identity/contact header, summary, experience, projects, education, skills, certificates, languages, and activities in that order. Optional empty sections are omitted; all required supported content wraps rather than truncates. Text is inert, dates retain source ISO values with an approved locale formatter, and only `https`, `http`, and `mailto` links may render as links; all other values remain text. Rich HTML is unsupported. | Pc, 2026-10-07 | 3.2, 3.3 |
| E3-DEC-005 | `approved` | `Pc` | Browser Export contract | MVP Export is an explicit user-initiated `window.print()` over the current successful Preview tuple, after print assets and document fonts are ready. It uses A4 portrait, 16 mm margins, document title `{version_name} - {template_name}`, hides application controls, and returns to `ready` or `unknown` after dialog return. Cancel/save completion is not observable and is never reported as an exported artifact. There is no server audit write, binary, download URL, worker, or provider call in MVP. | Pc, 2026-10-07 | 3.3 |
| E3-DEC-006 | `approved` | `Pc` | Accessibility and visual acceptance matrix | Chromium current stable is the automated visual/print baseline at 1440 px screen width and A4 portrait. Semantic DOM/content assertions are required in addition to visual snapshots. Keyboard tab order, visible focus, landmarks, heading outline, accessible control names, and status/error announcements are mandatory manual checks. Fixtures are synthetic and cover empty, minimal, full, long, Unicode, long URL, markup-like, and multi-page content. | Pc, 2026-10-07 | 3.1-3.3 verification |
| E3-DEC-007 | `approved` | `Pc` | Shared harness and CI commands | Reuse `E1-COORD-TEST-001` disposable PostgreSQL, Vite proxy, and Playwright harness; do not create a competing Compose stack. Codex is the proposed integration owner for Epic 3 fixture, visual-baseline, and cross-layer gate changes. Retain only CI artifacts from failed runs and accepted baselines; a repeatable failure blocks completion, while a one-off flaky run is rerun once with its artifact retained. | Pc, 2026-10-07 | all verification tasks |

## Implementation precision

- Each preview request supplies exactly one syntactically valid ULID
  `template_id` and one non-empty `template_version`; duplicate or malformed
  query values are `422 PREVIEW_SOURCE_INVALID`.
- A Template catalog entry has bounded inert text metadata only: `name` is 1-120
  characters, `description` is optional and at most 500 characters, and
  `preview_metadata` contains no URL, HTML, CSS, renderer key, or executable
  value. The initial MVP catalog publishes one active Template pair.
- A forward-only migration, not an edit to a historical migration, reconciles
  the existing `templates` and unused `cv_exports` scaffolds. It removes the
  unsupported browser-print completion lifecycle rather than recording a
  completion the browser cannot prove.
- Renderer output uses bundled application assets only. It must wait for
  `document.fonts` when available, continue with the approved bundled fallback
  when unavailable, and never load a remote font, image, stylesheet, or script.

## Cross-Epic prerequisite

### E3-PREREQ-VERSION-001 - Immutable CV Version render source

- Source: Epic 1 `E1-COORD-VERSION-001` and `E1-CONTRACT-VERSION-001`.
- Required checkpoint: approved complete immutable snapshot schema, owned detail
  operation, source naming, and backward-compatible reader policy.
- Status: satisfied by Epic 1 `E1-DEC-006`, `E1-COORD-VERSION-001`, and
  `docs/contracts/cv/version-v1.md` on 2026-10-07. Epic 3 consumes this
  contract without redefining the snapshot.

## Cross-Story coordination records

### E3-COORD-TEMPLATE-001 - Template catalog and version registry

- Stories: 3.1 owner; 3.2 and 3.3 consumers.
- Decision owner: `Pc`; implementation integration owner: `unassigned`.
- Resolution: approved by `E3-DEC-001`, `E3-DEC-002`, and `E3-DEC-003`.
- Reserved boundary: Template registry/config/model, catalog endpoint/resource,
  selection schema/state, metadata UI, and catalog fixtures.
- Sequence/merge rule: Story 3.1 freezes and lands the catalog checkpoint;
  consumers reference the exact identity/version without redefining it.

### E3-COORD-RENDER-001 - Shared Preview/Export renderer

- Stories: 3.2 owner; 3.3 consumer.
- Decision owner: `Pc`; implementation integration owner: `unassigned`.
- Resolution: approved by `E3-DEC-003`, `E3-DEC-004`, and `E3-DEC-006`.
- Reserved boundary: source adapter, section registry, renderer components,
  semantic structure, shared screen/print content, and renderer fixtures.
- Sequence/merge rule: Story 3.2 freezes the deterministic render checkpoint;
  Story 3.3 adds print presentation without forking content mapping.

### E3-COORD-PRINT-001 - Browser print surface

- Stories: 3.3, with 3.2 as reviewed-source provider.
- Decision owner: `Pc`; implementation integration owner: `unassigned`.
- Resolution: approved by `E3-DEC-005`, `E3-DEC-006`, and `E3-DEC-007`.
- Reserved boundary: print route/state, stylesheet, browser invocation wrapper,
  title/filename hints, print-emulation baselines, and retry copy.
- Sequence/merge rule: source tuple and renderer checkpoint precede print work;
  one owner integrates shared CSS and browser baselines.

### E3-COORD-TEST-001 - Epic 3 verification

- Stories: all Epic 3 Stories.
- Decision owner: `Pc`; implementation integration owner: `unassigned`.
- Resolution: approved by `E3-DEC-006`, `E3-DEC-007`, and accepted
  `E1-COORD-TEST-001` harness.
- Reserved boundary: Template/CV fixtures, visual snapshots, print emulation,
  Playwright browser configuration, synthetic User data, and CI evidence.
- Sequence/merge rule: one test-integration owner lands shared corpus/harness
  changes; Story owners add bounded scenarios without parallel config edits.

## Discovered work outside current Story scope

### DISCOVERY-E3-001 - Server-generated downloadable artifact

- Status: `unassigned`.
- Owner: `unassigned`.
- Scope: define measurable browser-export gaps, PDF artifact requirements,
  storage/expiry/download contract, worker boundary, observability, and cost.
- Reason externalized: `AD-10` makes browser print/HTML the MVP; server PDF is
  not authorized without an approved artifact requirement.
