# Story 3.3: Export a reviewed CV — Contract Slice

Return to the [Story overview](README.md). This slice refines
`E3-CONTRACT-EXPORT-001` and `E3-CONTRACT-ERROR-001`; exact values require
approval under `E3-DEC-003` through `E3-DEC-007`.

## Operation matrix

| Operation | Input/precondition | Success boundary | Failure boundary |
| --- | --- | --- | --- |
| Prepare Export | Exact ready `cv_version_id`, `template_id`, `template_version`, `renderer_version` | Print surface content equals reviewed projection and assets/styles reach approved ready state | Stale tuple, auth/source, renderer, asset, timeout, browser capability |
| Invoke browser print | Approved prepared surface and explicit User action | Dialog/action invoked; return to Preview context | Blocked/unsupported/exception with retry; no false artifact completion |
| Record intent, if approved | Server-derived User plus exact tuple; no CV body in logs | Intent-only audit, idempotent under approved key | Failure never blocks local print unless product explicitly approves it |

## FE/API responsibilities

- API continues to own authorization, immutable source truth, and current
  Template availability; for browser-only Export, protected Preview/source
  resolution is the authorization boundary rather than a fictional Export
  endpoint. It does not trust a client User ID or accept content for an artifact.
- Frontend proves the current ready tuple matches reviewed state, prepares the
  shared renderer, invokes print from a User action, and cleans up safely.
- Browser completion is unknown unless an approved platform contract proves it;
  copy and telemetry distinguish intent/invocation from file creation.

## Promotion gate

Freeze print API wrapper, browser/page matrix, source revalidation, stylesheet,
asset/font readiness, cancel/return semantics, filename/title hints, retry and
optional audit behavior, plus fixture evidence before `ready-for-dev`.
