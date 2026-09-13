---
epic_key: epic-3
title: Preview and Export an Application-Ready CV
created: 2026-09-12
source: _bmad-output/planning-artifacts/epics.md
stories:
  - 3-1-select-a-template
  - 3-2-preview-a-saved-cv-version
  - 3-3-export-a-reviewed-cv
---

# Epic 3: Preview and Export an Application-Ready CV

## Purpose

Epic 3 lets an authenticated User choose an available Template, render one
immutable CV Version for review, and export that reviewed source through the
browser print/HTML path without reading mutable Profile state.

This package is the shared planning contract for Stories 3.1 through 3.3. It
does not replace canonical intent in `epics.md`, approve open decisions, or
authorize implementation.

## Scope

- Discoverable active Template catalog with stable identity and version.
- One deterministic render projection from an owned immutable CV Version.
- Safe handling of optional, empty, long, and markup-like CV content.
- Accessible preview controls and semantic document structure.
- Browser print/HTML export of the exact reviewed CV Version and Template.
- Explicit loading, unavailable, stale, print-cancelled, and failure states.

## Out of scope

- Editing a CV Profile or Version from Preview.
- Server-side PDF, a worker renderer, file storage, email, or share links.
- AI-generated content, template design generation, or Match Report changes.
- Print-output guarantees outside an approved browser/viewport matrix.
- Stable production contracts under `docs/contracts/`; approved subsets must be
  promoted there before implementation.

## Source requirements

- `FR-10`: select an active Template and preview a saved CV Version.
- `FR-11`: export the reviewed source through browser print/HTML.
- `NFR-1`: ownership, non-disclosure, and safe content rendering.
- `NFR-2`: exact CV Version, Template, and template-version traceability.
- `NFR-3`: complete states, semantic structure, and keyboard access.
- `NFR-4`: explicit failures without partial or unexplained artifacts.
- `NFR-5`: repeatable output for unchanged sources and renderer version.

## Global and cross-Epic references

- Architecture: `AD-1` through `AD-4`, `AD-8`, `AD-10`, `AD-13` through
  `AD-19`.
- API/errors: `API-STD-001` through `API-STD-007`, `HTTP-CONTRACT-000`
  through `HTTP-CONTRACT-006`, and `ERROR-STD-001` through `ERROR-STD-003`.
- Security/data/reliability: `SEC-STD-001` through `SEC-STD-007`,
  `DATA-STD-001` through `DATA-STD-008`, and `REL-STD-001` through
  `REL-STD-005`.
- Accessibility/testing: `A11Y-STD-001` through `A11Y-STD-003` and
  `TEST-STD-001` through `TEST-STD-007`.
- Epic 1 consumer: `E1-CONTRACT-VERSION-001`, `E1-COORD-VERSION-001`, and
  `E1-COORD-TEST-001`.

## Epic package

| Artifact | Owns |
| --- | --- |
| [Business rules](business-rules.md) | Shared Template, Preview, Export, and source rules |
| [Contracts](contracts.md) | Shared catalog, render projection, print intent, and errors |
| [Data and lifecycle](data-and-lifecycle.md) | Template versioning and transient Preview/Export state |
| [Security and access](security-and-access.md) | Ownership, non-disclosure, safe rendering, and browser boundaries |
| [UX and validation](ux-and-validation.md) | Selection, preview, print, accessibility, and failure states |
| [Test strategy](test-strategy.md) | Snapshot fixtures, visual/print checks, and browser evidence |
| [Decisions](decisions.md) | Human-owned decisions and coordination records blocking readiness |

## Story packages

Each Story is one permanent folder under this Epic. Lifecycle remains only in
`sprint-status.yaml`.

| Story | Package |
| --- | --- |
| 3.1 Select a Template | [Open](stories/3-1-select-a-template/README.md) |
| 3.2 Preview a saved CV Version | [Open](stories/3-2-preview-a-saved-cv-version/README.md) |
| 3.3 Export a reviewed CV | [Open](stories/3-3-export-a-reviewed-cv/README.md) |

## Story map and ordering

```text
Epic 1 immutable CV Version ──┐
3.1 Template catalog ─────────┴──> 3.2 Preview ──> 3.3 Browser Export
```

- Story 3.1 establishes Template identity, version, availability, and selection.
- Story 3.2 consumes the exact CV Version and Template version through one
  shared render projection.
- Story 3.3 exports the same reviewed projection; it must not silently switch
  source, re-read Profile drafts, or introduce a worker dependency.

## Epic Definition of Ready

- Every applicable decision has an owner, approved resolution, and dated
  evidence.
- The Epic 1 immutable CV Version reader checkpoint is approved.
- Template, section projection, renderer version, print/browser matrix, and
  artifact acceptance are frozen in shared fixtures.
- Every canonical AC maps to atomic tasks and planned evidence.
- Template, renderer, print, and test boundaries have accepted coordination
  records.

## Epic Definition of Done

- All three canonical Stories are `done` with implementation and verification
  evidence.
- Preview and Export use exact owned CV Version and Template version sources.
- Unsaved Profile data never appears in the reviewed or exported output.
- Empty/long/markup-like content is safe, understandable, and print-stable.
- Ownership, component, accessibility, visual, print, and critical browser
  checks pass against the approved matrix.
