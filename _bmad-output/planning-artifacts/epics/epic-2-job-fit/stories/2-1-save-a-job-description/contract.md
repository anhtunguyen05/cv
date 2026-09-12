# Story 2.1: Save a Job Description — Contract

Return to the [Story overview](README.md). This file owns only the Story-specific
slice and inherits [Epic contracts](../../contracts.md).

## Operation matrix

| Operation | Proposed boundary | Request responsibility | Success responsibility | Failure responsibility |
| --- | --- | --- | --- | --- |
| Create Job Description | `POST /api/v1/job-descriptions` | `raw_text`, optional `company`/`role`, approved dedupe token; never owner/revision fields | `201` with `data` matching JD + immutable revision contracts | Validation, auth/session, throttle, unexpected failure, ambiguous success |
| Read Job Description | `GET /api/v1/job-descriptions/{jobDescription}` | Approved session; path ULID only | `200` with owned active/current representation and private cache behavior | Non-disclosing not found, auth/session, unexpected failure |

`E2-DEC-001`, `E2-DEC-002`, `E2-DEC-003`, and `E2-DEC-007` freeze exact
fields, limits, normalization, headers, statuses/codes, concurrency/deduplication,
and reconciliation behavior.

## FE/API integration rules

- The web client sends only approved fields with the Sanctum credential/CSRF
  boundary and validates successful data against shared fixtures.
- Stable code and field path control errors; messages are presentation only.
- After ambiguous success, the client performs the approved reconciliation
  query before resubmitting.
- The detail cache is keyed by logical Job Description ID plus approved current
  revision metadata; raw content is never placed in a cache key or URL.
- Both sides render source text as inert content and never reuse Analysis or
  Match Report shapes as the create response.

## Approval boundary

Before `ready-for-dev`, referenced decisions and `E2-PREREQ-AUTH-001` require
approved owner/evidence. Stable fixtures and contract text are promoted to the
approved `docs/contracts/` source and consumed by implementation tests.
