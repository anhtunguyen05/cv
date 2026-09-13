# Epic 3 Shared Contracts

All exact endpoints, fields, browser guarantees, and limits remain proposals
until approved in `decisions.md` and promoted to stable contract documentation.

## E3-CONTRACT-TEMPLATE-001 — Template summary

```json
{
  "id": "stable template key",
  "version": "immutable template version",
  "name": "localized display name",
  "description": "safe short description",
  "status": "active",
  "supported_sections": [],
  "preview_metadata": {}
}
```

- Catalog responses contain only active and available entries.
- Selection sends a Template identity/version, never executable markup, a file
  path, arbitrary component name, or client-defined renderer configuration.
- Availability is revalidated when Preview/Export begins; stale selection gets
  one explicit unavailable response.

## E3-CONTRACT-PREVIEW-001 — Render source and projection

```json
{
  "cv_version_id": "ULID",
  "template_id": "stable template key",
  "template_version": "string",
  "renderer_version": "string",
  "version_name": "string",
  "sections": [],
  "rendered_at": "UTC ISO-8601 or null for deterministic client projection"
}
```

- The API returns structured saved snapshot data; the browser renderer does not
  fetch mutable Profile fields to complete it.
- `sections` follows the approved registry, ordering, omission, date, URL, and
  text-safety rules.
- A direct Preview URL must resolve or reject the exact source tuple; it cannot
  fall back silently to another Version or current Template.

## E3-CONTRACT-EXPORT-001 — Browser export intent

The MVP export operation is a client-side print/HTML intent over a successful
Preview tuple. The contract freezes:

- source tuple equality between reviewed Preview and print surface;
- preparation state before `window.print()` or the approved equivalent;
- print stylesheet, title/filename hint, page size/margins, hidden controls, and
  link behavior;
- cancel, unsupported browser, blocked popup, render failure, and retry states;
- whether any server audit event is recorded without claiming file completion.

No binary file, download URL, server PDF status, or worker job exists in this
contract unless separately approved.

## E3-CONTRACT-ERROR-001 — Error mapping

| Condition | Proposed status/code | Required UI behavior |
| --- | --- | --- |
| Unauthenticated/expired | global auth contract | Recover session without leaking source |
| Missing/foreign CV Version | `404 CV_VERSION_NOT_FOUND` | Non-disclosing unavailable state |
| Inactive/unavailable Template | `409 TEMPLATE_UNAVAILABLE` | Preserve selection context and return to catalog |
| Unsupported Template/source schema | `422 RENDER_SOURCE_UNSUPPORTED` | Explain incompatibility; do not partially render |
| Unsafe/invalid source projection | `422 PREVIEW_SOURCE_INVALID` | Safe API error with no executable fragment |
| API projection construction failure | `500 PREVIEW_PROJECTION_FAILED` or approved retryable equivalent | Preserve exact source; actionable retry |
| Client renderer exception | client `PREVIEW_RENDER_FAILED` state | Stop partial presentation, preserve exact tuple, and offer safe retry |
| Print unsupported/blocked | client capability code | Explain browser requirement and fallback |

Exact topology, envelopes, and codes remain open under `E3-DEC-001` and
`E3-DEC-005`.

## Proposed operation inventory

| Operation | Proposed boundary | Purpose |
| --- | --- | --- |
| List Templates | `GET /api/v1/templates` | Return selectable active Template summaries |
| Resolve Preview source | `GET /api/v1/cv-versions/{cvVersion}` plus approved Template selection | Return owned immutable render projection |
| Open Preview | approved client route | Render the exact source tuple |
| Export | browser print/HTML action | Print the already reviewed tuple |
