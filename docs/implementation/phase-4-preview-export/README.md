# Phase 4 — Template Preview and Export

Status: `planned`  
**Depends on:** Phase 1 CV Version foundation

## Outcome

An approved immutable CV version can be rendered using a selected template and
exported through the MVP browser print/HTML path without relying on unsaved
editor state or an AI provider. Worker/server-side Export is later.

## Design

Add `templates` for the MVP. An optional post-MVP `export_jobs` resource may be
introduced later; it is not required for MVP Preview/Export. Templates contain
configuration/assets, not copies of user CV data. Render one responsive
HTML/CSS template first, expose a print-friendly route, and use browser
print/HTML Export for the MVP. Introduce queued worker/server-side Export only
when artifact generation requires it.

MVP Preview/Export must not require background export jobs, queue
infrastructure, or worker-side Export persistence.

The renderer consumes a version snapshot and template ID. It must handle empty
optional sections, long text, page breaks, and safe escaping. Never use an
editable PDF as the canonical source.

## API and frontend

Implement template list/detail and browser print/HTML Export. Add template
selection, live preview, print/export action, error state, and retry. An Export
must identify the exact CV Version and Template used. Export status/create
endpoints backed by `export_jobs` are post-MVP only.

## Verification

```powershell
cd apps/api; php artisan test
cd ../web; npm run type-check; npm run build; npm run lint
```

Add render snapshots/manual checks for required sections, another user's export,
failed export retry, and unsaved-editor-state isolation.

## Checkpoint / exit criteria

- One approved version renders consistently in preview and print.
- Exported output uses the selected snapshot, not draft browser state.
- Export errors are visible and retryable.
- No AI key or worker is required for the first export path.

See [`github-issues.md`](github-issues.md).
