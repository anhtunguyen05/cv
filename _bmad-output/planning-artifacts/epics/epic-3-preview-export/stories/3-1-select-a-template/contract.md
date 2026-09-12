# Story 3.1: Select a Template — Contract Slice

Return to the [Story overview](README.md). This slice refines
`E3-CONTRACT-TEMPLATE-001` and `E3-CONTRACT-ERROR-001`; exact values require
approval under `E3-DEC-001` through `E3-DEC-003`.

## Operation matrix

| Operation | Proposed request | Success | Failure |
| --- | --- | --- | --- |
| List available Templates | `GET /api/v1/templates` with no owner input | `200` enveloped ordered active summaries | Auth, safe catalog failure |
| Validate selection for Preview | Exact `template_id` and `template_version` with owned `cv_version_id` at Preview boundary | Accepted exact tuple; no side-effect resource | Missing/foreign CV, inactive/unavailable/incompatible/stale Template |

## FE/API responsibilities

- API owns registry trust, availability, compatibility, deterministic order,
  identity/version, and safe serialization.
- Frontend validates response shape, renders metadata as inert content, tracks
  the exact selected tuple, and handles empty/error/stale states.
- Neither layer silently selects a default or substitutes the newest Template
  version after the User reviewed another entry.

## Promotion gate

Freeze example payloads, metadata limits, availability semantics, cache policy,
selection route/state, status/error matrix, and fixtures before promotion to a
stable contract or `ready-for-dev`.
