# Sprint 16 — API Architecture Boundary Hardening

## Sprint goal

Establish and incrementally apply the API's module-level Ports-and-Adapters
boundary while preserving all existing product contracts and regression
evidence.

## Dates and capacity

- Dates: to be scheduled after Epic 6 story decomposition and approval.
- Capacity: one backend implementer and one reviewer; feature slices advance
  only after their dependencies and verification gates are satisfied.

## Constraints

- No public contract, schema, migration, dependency, provider behavior, or
  existing Epic 2/4/5 scope changes.
- PostgreSQL 16 remains the integration authority; SQLite is only a fast local
  test harness.
- Every slice must pass the recursive boundary test, Pint, and its focused
  feature corpus before the next slice starts.

## Committed story keys

No story keys are committed yet. The Epic 6 package must be decomposed and
approved through the canonical epics and sprint-status workflows before this
charter receives membership.

## Verification gates

- `./scripts/e1-verify.sh api-fast`
- `./scripts/e1-verify.sh api-pg`
- `./scripts/e1-verify.sh e2e`
- `git diff --check`
