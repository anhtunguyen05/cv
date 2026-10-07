# Epic 3 Shared Contracts

The following is the approved Epic 3 planning baseline. It must be promoted to
stable contract documentation before implementation; no application endpoint is
claimed as implemented by this artifact.

## E3-CONTRACT-TEMPLATE-001 - Template summary

```json
{
  "id": "ULID template key",
  "version": "immutable published version",
  "name": "localized display name",
  "description": "safe short description",
  "status": "active",
  "supported_sections": [],
  "preview_metadata": {}
}
```

- `GET /api/v1/templates` returns `{ "data": [TemplateSummary] }` under the
  common envelope and `Cache-Control: private, no-store`, ordered `name ASC,
  id ASC`.
- Catalog responses contain only active and available entries compatible with
  snapshot schema `1.0`.
- Selection sends a Template identity/version, never executable markup, a file
  path, arbitrary component name, or client-defined renderer configuration.
- Availability is revalidated when Preview/Export begins; stale selection gets
  one explicit unavailable response.
- `name` is 1-120 characters; `description` is optional and at most 500
  characters. `preview_metadata` contains inert presentation values only and
  never a URL, HTML, CSS, renderer key, or executable value.

## E3-CONTRACT-PREVIEW-001 - Render source and projection

```json
{
  "cv_version_id": "ULID",
  "template_id": "ULID",
  "template_version": "string",
  "renderer_version": "1.0.0",
  "version_name": "string",
  "sections": [],
  "rendered_at": null
}
```

- `GET /api/v1/cv-versions/{cvVersion}/preview?template_id={ULID}&template_version={string}`
  returns the common `data` envelope and revalidates the exact source tuple.
- `template_id` occurs exactly once and is a ULID; `template_version` occurs
  exactly once and is non-empty. A malformed or duplicate query value returns
  `422 PREVIEW_SOURCE_INVALID`.
- The API returns structured saved snapshot data; the browser renderer does not
  fetch mutable Profile fields to complete it.
- `sections` follows the approved registry, ordering, omission, date, URL, and
  text-safety rules.
- A direct Preview URL must resolve or reject the exact source tuple; it cannot
  fall back silently to another Version or current Template.

## E3-CONTRACT-EXPORT-001 - Browser export intent

The MVP export operation is a client-side print/HTML intent over a successful
Preview tuple. It creates no server audit record, binary, download URL, worker
job, or provider call. The contract freezes:

- source tuple equality between reviewed Preview and print surface;
- preparation state before `window.print()`;
- A4 portrait, 16 mm margins, title/filename hint, hidden controls, and link
  behavior;
- cancel, unsupported browser, blocked dialog, render failure, and retry states.
- bundled-only assets and fonts; wait for `document.fonts` when available, then
  use the approved bundled fallback without a remote request when it is not.

Browser dialog return is `unknown`, not a completed export; cancel/save
completion cannot be inferred.

## E3-CONTRACT-ERROR-001 - Error mapping

| Condition | Status/code | Required UI behavior |
| --- | --- | --- |
| Unauthenticated/expired | global auth contract | Recover session without leaking source |
| Missing/foreign CV Version | `404 CV_VERSION_NOT_FOUND` | Non-disclosing unavailable state |
| Inactive, stale, unavailable, or incompatible Template | `409 TEMPLATE_UNAVAILABLE` | Preserve Version context and return to catalog |
| Unsupported Template/source schema | `422 RENDER_SOURCE_UNSUPPORTED` | Explain incompatibility; do not partially render |
| Unsafe/invalid source projection | `422 PREVIEW_SOURCE_INVALID` | Safe API error with no executable fragment |
| API projection construction failure | `500 PREVIEW_PROJECTION_FAILED` | Preserve exact source; actionable retry |
| Client renderer exception | client `PREVIEW_RENDER_FAILED` state | Stop partial presentation, preserve exact tuple, and offer safe retry |
| Print unsupported/blocked | client `PRINT_UNAVAILABLE` | Explain browser requirement and preserve Preview |

## Approved operation inventory

| Operation | Boundary | Purpose |
| --- | --- | --- |
| List Templates | `GET /api/v1/templates` | Return selectable active Template summaries |
| Resolve Preview source | `GET /api/v1/cv-versions/{cvVersion}/preview?template_id={ULID}&template_version={string}` | Return owned immutable render projection |
| Open Preview | `/cv-versions/{cvVersion}/preview?template_id={ULID}&template_version={string}` | Render/revalidate the exact source tuple |
| Export | browser print/HTML action | Print the already reviewed tuple |
