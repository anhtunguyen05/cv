# Epic 6 — API Architecture Boundary Hardening

## Outcome

The Laravel API is organized as module-level Ports-and-Adapters while its
public routes, JSON envelopes, persistence values, authorization behavior, and
failure semantics remain compatible with the completed product epics.

## Scope

- Application use cases depend on contracts, DTOs, domain policies, and value
  objects rather than Eloquent models, database facades, or HTTP clients.
- Infrastructure owns Eloquent mappings, transactions, locks, outbound HTTP,
  and composition-root bindings.
- Presentation maps requests, results, and application errors to the existing
  HTTP contract; it does not query persistence.
- Domain status values are backed enums whose values are the existing persisted
  strings. No migration or backfill is required.
- A recursive architecture test enumerates nested PHP directories and fails on
  prohibited imports or missing composition bindings.

## Explicit exclusions

This epic does not change routes, response envelopes, status/error codes,
authorization, database schema, persisted string values, provider behavior, or
the scope of Epics 2, 4, and 5. It does not introduce API v2, generic CRUD
repositories, aggregate/entity mapping, destructive data operations, or a new
dependency.

## Delivery shape

Stories are decomposed into the architecture foundation, CV/Auth, Job Fit,
Evidence, Patch, and Operational Safety slices. Each slice must preserve the
existing feature corpus before the next slice is advanced.

## Authority

The approved implementation specification is
`_bmad-output/implementation-artifacts/spec-api-architecture-boundary-hardening.md`.
Stable cross-epic rules live in `docs/architecture/` and `docs/standards/`.
