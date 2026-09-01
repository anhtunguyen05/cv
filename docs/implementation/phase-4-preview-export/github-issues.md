# GitHub Issues — Phase 4

Suggested labels: `phase-4`, `templates`, `export`, `frontend`, `backend`.

## #P4-1 — Add MVP template catalog

**Depends on:** Phase 1 CV Version foundation  
**Labels:** `phase-4`, `database`, `backend`

- [ ] Add the `templates` migration.
- [ ] Add the Template model, relations, casts, indexes, and active-state validation.
- [ ] Add Template selection and lifecycle tests.

**Acceptance:** the MVP has an active Template that can be selected for a saved
CV Version; no `export_jobs` or Export record is required.

## #P4-2 — Build the first CV renderer

**Depends on:** `#P4-1`  
**Labels:** `phase-4`, `frontend`, `templates`

- [ ] Add one responsive HTML/CSS template.
- [ ] Render all initial structured sections safely.
- [ ] Handle empty sections, long content, and print page breaks.
- [ ] Add snapshot/manual render checks.

**Acceptance:** the same immutable data produces stable preview output.

## #P4-3 — Implement template selection API

**Depends on:** `#P4-1`, `#P4-2`  
**Labels:** `phase-4`, `backend`, `security`

- [ ] Add template list/detail endpoints.
- [ ] Add authorization and active Template validation.

**Acceptance:** the API exposes only active, selectable Templates and never
requires an Export job or Export record for the MVP.

## #P4-4 — Add preview and browser export UX

**Depends on:** `#P4-3`  
**Labels:** `phase-4`, `frontend`

- [ ] Add template selection and preview route.
- [ ] Add browser print/download action.
- [ ] Show Preview and browser print/error states.
- [ ] Verify output uses selected version.

**Acceptance:** user can preview and export one approved version without AI.

## #P4-5 — Phase 4 checkpoint

**Depends on:** `#P4-2`, `#P4-4`  
**Labels:** `phase-4`, `testing`

- [ ] Run backend and frontend checks.
- [ ] Perform output, ownership, and unsaved-state smoke tests.

**Acceptance:** all phase exit criteria pass.

## Post-MVP Export Expansion

The following work is explicitly outside MVP Phase 4:

- Add `export_jobs` persistence and Export records.
- Add asynchronous Export creation and status endpoints.
- Add queue-backed Export processing.
- Add worker/server-side PDF generation and persistence.

These capabilities may be considered only if browser print/HTML fails an
approved artifact requirement. They must not become prerequisites for MVP
Preview or Export.
