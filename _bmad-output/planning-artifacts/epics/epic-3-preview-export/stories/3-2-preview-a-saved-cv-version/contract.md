# Story 3.2: Preview a saved CV Version — Contract Slice

Return to the [Story overview](README.md). This slice refines
`E3-CONTRACT-PREVIEW-001` and `E3-CONTRACT-ERROR-001`; exact values require
approval under `E3-DEC-001` through `E3-DEC-004`.

## Operation matrix

| Operation | Proposed request | Success | Failure |
| --- | --- | --- | --- |
| Resolve Preview | Exact owned `cv_version_id`, `template_id`, `template_version` | `200` structured immutable projection with `renderer_version` | Auth, non-disclosing Version, Template unavailable/incompatible, invalid source, renderer failure |
| Open direct Preview route | Exact source tuple in approved route state | Render same validated projection after refresh | Never fall back to draft/current/other Template |

## FE/API responsibilities

- API owns session, CV Version ownership, immutable snapshot serialization,
  Template availability/compatibility validation, and safe error envelope.
- Shared frontend adapter validates projection/schema and source tuple before
  renderer components consume it.
- Renderer owns presentation only; it does not fetch Profile data, infer missing
  source values, mutate canonical content, or create Export state.

## Promotion gate

Freeze example payloads, complete section registry, source-schema compatibility,
Template/renderer versioning, route/cache behavior, status/error matrix, content
safety, accessibility, and visual fixtures before `ready-for-dev`.
