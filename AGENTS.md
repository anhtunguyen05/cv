<!-- bmad:context -->
<!-- Verified 2026-09-08 against e7067d6. Managed by bmad-project-context; edits inside this block are replaced on refresh. -->

## CareerFitCV

CareerFitCV is a Vue 3/Vite frontend, Laravel 13 API, and minimal Python worker repository. The current codebase is an early scaffold. Current product planning and story breakdown are managed through BMAD artifacts under `_bmad-output/`; keep planned behavior separate from implemented behavior.

## Policy

- Never push directly to `main`; use a reviewable branch and pull request for each cohesive planning or implementation change.
- When the assigned scope is story breakdown, use BMAD workflows and modify planning artifacts only; do not change application, infrastructure, automation, or validation code.
- Treat `_bmad-output/planning-artifacts/epics.md` as the canonical story definition and `_bmad-output/implementation-artifacts/sprint-status.yaml` as the lifecycle tracker.
- Do not use `docs/implementation-plan.md` or `docs/implementation/` to select, order, or decompose current work; those files are legacy phase references.
- Multiple stories and tasks may be active concurrently. Every task marked `doing` must identify one owner, its story, dependencies, branch or worktree, and declared scope in the BMAD story artifact.
- Coordinate before starting tasks whose file, contract, persistence, or domain scope overlaps; independent tasks with satisfied dependencies may proceed in parallel.
- Do not mark a story `ready-for-dev` until its behavior, contracts, backend rules, security, validation, frontend, integration, acceptance criteria, dependencies, and verification coverage are explicit.
- Keep tasks atomic, dependency-aware, independently assignable, and independently verifiable; do not impose a repository-wide single-active-task rule.
- Do not expand story scope silently; record independent discovered work separately and keep the current story within its approved boundary.

## Where things are

- BMAD PRD: `_bmad-output/planning-artifacts/prds/prd-CareerFitCV-2026-09-01/`
- BMAD architecture spine: `_bmad-output/planning-artifacts/architecture/architecture-CareerFitCV-2026-09-01/ARCHITECTURE-SPINE.md`
- Canonical epics and stories: `_bmad-output/planning-artifacts/epics.md`
- Story lifecycle tracker: `_bmad-output/implementation-artifacts/sprint-status.yaml`
- Story breakdown artifacts: `_bmad-output/implementation-artifacts/`
- Story breakdown protocol: `docs/story-execution.md`
- Frontend work and conventions: `apps/web/src/`, `apps/web/package.json`, and `docs/frontend-architecture.md`
- Laravel API work: `apps/api/`; read `apps/api/AGENTS.md` before changing files under `apps/api/`
- Worker work: `apps/worker/app/`; worker tests: `apps/worker/tests/`
- Current and target technical documentation: `docs/architecture.md`, `docs/api.md`, `docs/database.md`, `docs/ai-workflow.md`, and `docs/decisions.md`

## Running and verifying

- Verify the Laravel API endpoint as `/api/health`; `/health` is the worker alias, not the Laravel API route.
- Treat `apps/api/docker-compose.yml` as the API-local Docker entry point; no root-level Compose file exists.
- For planning-only changes, validate completeness and consistency through BMAD review; do not install or run application dependencies unless the planning task specifically requires them.

## Conventions that differ from defaults

- Preserve the current-versus-target distinction: planned CV, JD, matching, AI, patch, template, and export features are not implemented until code and verification exist.
- Treat CV versions as immutable snapshots when implementing the planned versioning model.
- Keep AI output proposal-based and human-approved; providers must not write directly to persistence.
- Keep product API routes under the planned `/api/v1` boundary; preserve the existing `/api/health` endpoint.
- Prefer the minimal worker approach until a real PDF, parsing, or background-processing requirement is implemented.

## Known pitfalls

- Do not treat legacy phase files under `docs/implementation/` as current BMAD planning authority.
- Do not document or test Laravel health as `GET /health`; the current Laravel route is `GET /api/health`.
- Do not assume README target directories such as `packages/`, `infra/`, or root Docker files exist.
- Do not install Laravel Boost for planning-only or documentation-only work.

<!-- /bmad:context -->
