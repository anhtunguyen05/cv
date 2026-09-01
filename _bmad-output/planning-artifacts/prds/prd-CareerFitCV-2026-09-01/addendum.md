# Addendum — CareerFitCV Canonical PRD

This addendum preserves implementation and source-reconciliation detail that
would make the PRD's requirements less clear. It is not a second requirements
source; if it conflicts with `prd.md`, the PRD and its recorded decisions win.

## 1. Source reconciliation

### Retained decisions

- Structured CV data is preferred over free-form CV text.
- CV Versions are immutable snapshots.
- Match Reports must be explainable and tied to an exact CV Version and Job
  Description.
- Later AI output is proposal-based and requires User approval.
- A single orchestrator is preferred before multi-agent decomposition.
- Current scaffold status must remain separate from target product behavior.

### Resolved contradictions

- The canonical current Laravel health route is `/api/health`; `/health` is the
  worker alias. Product routes are planned under `/api/v1`.
- Deterministic matching is the MVP behavior. LLM analysis is not an MVP
  dependency.
- MVP Export is browser print/HTML. Server-side or worker PDF generation is a
  later option.
- The MVP uses a mutable CV Profile plus immutable named CV Versions.
- The Python worker is currently only a health service. It is not treated as a
  PDF or parsing dependency.

## 2. Implementation mapping

The implementation sequence remains in `docs/implementation-plan.md` and the
phase packages remain in `docs/implementation/`:

| PRD capability | Implementation package | Notes |
| --- | --- | --- |
| Account access and API boundary | Phase 0 | Auth mechanism remains a Phase 0 decision |
| CV Profile and CV Version | Phase 1 | Draft Profile plus immutable Versions |
| Job Description and Match Report | Phase 2 | Deterministic, provider-free first |
| Template Preview and browser Export | Phase 4 | Pull forward as MVP completion after core matching |
| AI interview and Patch | Phase 3 | Post-MVP; requires explicit approval |
| Audit, retention, advanced AI | Phase 5 | Post-MVP hardening |

The original implementation plan places Template/Export after AI Patch work.
The canonical PRD intentionally changes release sequencing so Preview/Export
completes the MVP while AI Patch work remains later. This is a release-scope
decision, not a change to the underlying architecture.

## 3. Proposed MVP data boundaries

The MVP needs the following conceptual records:

- User
- CV Profile
- CV Version
- Job Description
- Match Report
- Template
- Export

Later records include AI interview sessions, interview messages, Patches, and
AI tool-call audit records. JSON is appropriate for variable CV sections and
derived analysis, but each JSON contract needs a schema version and server-side
validation before implementation is considered complete.

## 4. Implementation constraints

- The Laravel API owns trusted state transitions and User ownership checks.
- The web client consumes stable API resources rather than duplicating domain
  rules locally.
- Matching rules, aliases, and weights must be versioned so old Match Reports
  remain interpretable.
- Preview and Export consume saved CV Version snapshots, never unsaved editor
  state.
- Later provider adapters return validated data transfer objects; they do not
  persist directly.
- Patch application, when implemented, must revalidate stale source values and
  create a new CV Version transactionally.

## 5. Known repository baseline

The current repository contains Vue starter views, a Laravel 13 skeleton and
default migrations, and a Python stdlib worker exposing health endpoints. There
are no implemented CV, Job Description, Match Report, Template, Export,
interview, or Patch domain modules at the time this PRD was created.

The repository-local implementation instructions are in `AGENTS.md` and
`apps/api/AGENTS.md`. The latter is relevant when application files under
`apps/api/` are changed; it does not turn planned features into implemented
features.
