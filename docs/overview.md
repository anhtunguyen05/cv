# CareerFitCV Documentation Map

`docs/` is the stable, implementation-facing source of truth. It records rules
shared across epics; it does not replace the canonical backlog, story contracts,
or sprint lifecycle artifacts in `_bmad-output/`.

## Current implementation

- `apps/web`: Vue 3/Vite application foundation.
- `apps/api`: Laravel 13 foundation with `GET /api/health`.
- `apps/worker`: optional Python health service.
- Product domain behavior is planned, not implemented, until code and
  verification demonstrate it.

## Read by intent

| Need | Canonical source |
| --- | --- |
| System boundaries and deployment posture | [architecture/overview.md](architecture/overview.md) |
| Durable architecture decisions | [architecture spine](../_bmad-output/planning-artifacts/architecture/architecture-CareerFitCV-2026-09-01/ARCHITECTURE-SPINE.md) and [ADR policy](architecture/adr/README.md) |
| HTTP envelope, errors, versioning and pagination | [contracts/common/http.md](contracts/common/http.md) |
| API implementation rules | [standards/api.md](standards/api.md) |
| Authentication, authorization and sensitive data | [standards/security.md](standards/security.md) |
| IDs, timestamps, persistence and lifecycle rules | [standards/data.md](standards/data.md) |
| Validation and error ownership | [standards/validation.md](standards/validation.md) and [standards/errors.md](standards/errors.md) |
| Tests and acceptance evidence | [standards/testing.md](standards/testing.md) |
| Accessibility and UI states | [standards/accessibility.md](standards/accessibility.md) |
| Logging, audit and operations | [standards/observability.md](standards/observability.md) and [standards/reliability.md](standards/reliability.md) |
| Post-MVP asynchronous work | [contracts/common/async-job.md](contracts/common/async-job.md) |
| Shared product vocabulary | [domain/glossary.md](domain/glossary.md) |
| Story and sprint process | [story-execution.md](story-execution.md), [sprint-workflow.md](sprint-workflow.md) |

## Boundary of this documentation

Global docs define reusable rules. An epic owns rules shared only by its
stories; a story owns user-visible behavior, acceptance criteria, and its task
breakdown. Every lower-level artifact references global rule IDs rather than
copying their contents.
