<!-- bmad:context -->
<!-- Verified 2026-09-01 against deaf546c939db3fb34e5074c41594bf143207dc8. Managed by bmad-project-context; edits inside this block are replaced on refresh. -->

## CareerFitCV

CareerFitCV is a Vue 3/Vite frontend, Laravel 13 API, and minimal Python worker repository. The current codebase is an early scaffold; product behavior is planned in `docs/implementation-plan.md` and phase execution packages under `docs/implementation/`. Keep implementation status separate from target architecture.

## Where things are

- Frontend work: `apps/web/src/`; frontend commands and dependencies: `apps/web/package.json`
- Laravel API work: `apps/api/`; read `apps/api/AGENTS.md` before changing files under `apps/api/`
- Worker work: `apps/worker/app/`; worker tests: `apps/worker/tests/`
- Architecture and current/target boundaries: `docs/architecture.md`
- API contracts and endpoint status: `docs/api.md`
- Database design: `docs/database.md`
- AI workflow and patch safety: `docs/ai-workflow.md`
- Executable implementation sequence: `docs/implementation-plan.md`
- Phase-level implementation tasks and GitHub issues: `docs/implementation/`
- Architecture decisions: `docs/decisions.md`

## Running and verifying

- Verify the Laravel API endpoint as `/api/health`; `/health` is the worker alias, not the current Laravel API route.
- Treat `apps/api/docker-compose.yml` as the API-local Docker entry point; do not assume a root-level Compose file exists.
- Use the application-specific commands documented in each manifest and README.

## Conventions that differ from defaults

- Preserve the current-versus-target distinction: planned CV, JD, matching, AI, patch, template, and export features are not implemented until code and verification exist.
- Treat CV versions as immutable snapshots when implementing the planned versioning model.
- Keep AI output proposal-based and human-approved; providers must not write directly to persistence.
- Keep product API routes under the planned `/api/v1` boundary; preserve the existing `/api/health` endpoint.
- Prefer the minimal worker approach until a real PDF, parsing, or background-processing requirement is implemented.

## Known pitfalls

- Do not document or test Laravel health as `GET /health`; the current Laravel route is `GET /api/health`.
- Do not assume README target directories such as `packages/`, `infra/`, or root Docker files exist.
- Do not install Laravel Boost for documentation-only changes; the nested Laravel instruction applies to application work under `apps/api/`.

<!-- /bmad:context -->
