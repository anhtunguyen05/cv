# Epic 3 Context: Preview and Export an Application-Ready CV

<!-- Compiled from planning artifacts. Edit freely. Regenerate with compile-epic-context if planning docs change. -->

## Goal

Enable an authenticated user to select an active, compatible Template, preview an owned immutable CV Version, and export that exact reviewed source through the browser print/HTML path. This completes the MVP presentation flow while preserving source integrity, safe deterministic rendering, accessibility, and traceability; AI providers, worker rendering, server PDF, and stored artifacts are outside this epic.

## Stories

- Story 3.1: Select a Template
- Story 3.2: Preview a saved CV Version
- Story 3.3: Export a reviewed CV

## Requirements & Constraints

- The Template catalog exposes only active, available pairs compatible with the CV snapshot schema. Selection must identify a stable Template and immutable version; inactive, stale, unknown, or incompatible selections are rejected.
- Preview and Export use only the authenticated user’s owned immutable CV Version snapshot. They must never merge mutable or unsaved CV Profile values, silently switch versions/templates, or disclose foreign resources.
- Render all supported non-empty CV sections through one approved section registry. Omit optional empty sections without broken headings or page fragments; preserve required content with deterministic ordering, wrapping, date, link, overflow, and page-break rules.
- Treat CV content and metadata as untrusted. Render text inertly (rich HTML is unsupported); only approved `http`, `https`, and `mailto` links may be links. Fail closed on invalid/unsafe source data, renderer errors, or asset failures.
- Provide loading, empty, unavailable, unsupported, stale, renderer-failure, print-blocked, print-unsupported, and retryable failure states. Never claim a successful export when browser print completion or save/cancel cannot be observed, and do not create unexplained duplicate persistent state.
- Enforce server-side ownership and authorization, avoid sensitive CV content in ordinary logs/telemetry, and use synthetic data for visual fixtures. Browser output must not expose controls, tokens, debug values, or unrelated open-source data.
- MVP Export is user-initiated browser print/HTML and requires no AI provider or worker. Server-side PDF, file storage, sharing, and background rendering are separate discovered work.

## Technical Decisions

- Keep Laravel as the authoritative API/domain boundary for authentication, ownership, validation, and trusted state; Vue owns presentation state. Use the versioned `/api/v1` envelope and ULID product identifiers.
- `GET /api/v1/templates` returns `{data: [TemplateSummary]}` in deterministic `name ASC, id ASC` order with private no-store caching. Preview resolves `GET /api/v1/cv-versions/{cvVersion}/preview?template_id={ULID}&template_version={string}` and revalidates the exact source tuple. Foreign/missing versions use a non-disclosing `404 CV_VERSION_NOT_FOUND`; unavailable templates use `409 TEMPLATE_UNAVAILABLE`.
- A Template is an application-owned `(template_id, template_version)` pair. Published pairs are immutable, expose bounded inert metadata and server-owned renderer mappings, and support snapshot schema `1.0`. Behavior changes publish a new pair; the existing schema must be reconciled through a forward migration before implementation.
- The shared renderer is version `1.0.0` and supports identity/contact, summary, experience, projects, education, skills, certificates, languages, and activities in that order. Preview and print share this projection; source data, display labels, and print formatting remain distinct.
- Export prepares the reviewed tuple, bundled assets, and fonts, then invokes `window.print()` using A4 portrait, 16 mm margins, a `{version_name} - {template_name}` title, hidden application controls, and approved link behavior. Dialog return is `unknown`; no server audit, binary, download URL, worker job, or provider call is implied.
- Verification is risk-based: backend feature/contract tests, Vue component tests, semantic/accessibility assertions, visual and print-emulation checks, and Playwright critical journeys use shared synthetic fixtures for empty, minimal, full, long, Unicode, markup-like, long-URL, and multi-page snapshots.

## UX & Interaction Patterns

The flow is saved CV Version → active Template catalog → selected Template → exact Preview → browser Export, with the Version and Template context preserved when returning from print. Template cards expose selection and unavailable states without relying on color alone. Preview uses one meaningful heading hierarchy, semantic sections and landmarks, named links and controls, keyboard operation, visible focus, and announced async errors. Export stays disabled or guarded until the exact Preview is ready; blocked or unsupported print explains the requirement and preserves a safe retry path. Screen-only and print-only regions must not duplicate assistive content.

## Cross-Story Dependencies

- Epic 1’s approved immutable CV Version snapshot schema, owned reader, source naming, and compatibility policy are the prerequisite source contract; Epic 3 consumes them without redefining them.
- Story 3.1 freezes the Template catalog, identity/version, availability, and selection checkpoint. Story 3.2 consumes that exact pair and owns the deterministic section registry/render projection. Story 3.3 consumes the reviewed projection and adds print presentation without forking content mapping.
- Shared Template fixtures, renderer fixtures, print styles, browser configuration, and Playwright harness changes require one coordinated integration owner and the approved verification checkpoints. A server-downloadable/PDF artifact is tracked separately from this epic.
