---
title: 'Standardize CV and Match route page wrappers'
type: 'refactor'
created: '2026-10-08'
status: 'done'
review_loop_iteration: 0
context:
  - '{project-root}/apps/web/vue-frontend-architecture.md'
---

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

**Problem:** Several CV and Match route pages still own large templates and, in two cases, query orchestration while editor, dashboard, and JD routes already use thin feature-screen wrappers. The inconsistent boundary makes route files harder to review and allows server-state concerns to drift back into pages.

**Approach:** Move the existing CV preview/version, Patch Review, and Match Report presentation into feature screen components without changing route paths, API contracts, controller behavior, or user-visible flows. Leave each route page as a single feature import and render.

## Boundaries & Constraints

**Always:** Preserve templates, text, route constants, controller/query behavior, lifecycle events, and existing tests. Keep server-state access inside feature components/controllers.

**Ask First:** Any backend/API contract change, route change, UX redesign, dependency addition, or behavior change that discards user input.

**Never:** Do not change Laravel code, CSRF behavior, route URLs, mutation contracts, or unrelated feature screens.

</frozen-after-approval>

## Code Map

- `apps/web/src/pages/cv/CvPreviewPage.vue` -- route entry; move version list UI and query into `features/cv/components/CvPreview.vue`.
- `apps/web/src/pages/cv/CvVersionPage.vue` -- route entry; move preview query, renderer lifecycle, and document UI into `features/cv/components/CvVersionPreview.vue`.
- `apps/web/src/pages/cv/PatchReviewPage.vue` -- route entry; move existing controller-backed proposal UI into `features/evidence/components/PatchReview.vue`.
- `apps/web/src/pages/match/MatchReportListPage.vue` -- route entry; move pagination/query/list UI into `features/match/components/MatchReportList.vue`.
- `apps/web/src/pages/match/MatchReportPage.vue` -- route entry; move existing controller-backed detail UI into `features/match/components/MatchReportDetail.vue`.
- `apps/web/eslint.config.ts` -- page boundary; forbid feature query imports in route pages.
- `apps/web/tests/web-architecture-boundaries.test.ts` -- enforce thin wrapper and query boundary rules.
- `apps/web/vue-frontend-architecture.md` -- document the wrapper-only route-page rule.

## Tasks & Acceptance

**Execution:**

- [x] Add feature screen components by relocating existing page scripts/templates without behavior changes.
- [x] Replace the five route page bodies with thin feature imports and renders.
- [x] Extend lint, architecture tests, and architecture documentation to enforce the boundary.
- [x] Run type-check, lint, unit tests, build, and diff checks.

**Acceptance Criteria:**

- Given any CV or Match route above, when its route module is loaded, then the page contains only a feature-screen import and render.
- Given a feature screen needs server state, when it loads or mutates data, then query/controller code remains inside the feature boundary and no page imports a feature query module.
- Given existing CV preview, Patch Review, or Match Report flows, when the refactor is applied, then route navigation, loading/error states, pagination, preview events, and mutation callbacks behave as before.
- Given a future page imports a feature query module directly, when lint or architecture tests run, then the boundary violation is reported.

## Verification

**Commands:**

- `npm run type-check --prefix apps/web` -- expected: Vue/TypeScript compilation succeeds.
- `npm run lint --prefix apps/web` -- expected: ESLint and oxlint succeed.
- `npm run test:unit --prefix apps/web -- --run` -- expected: all existing and boundary tests pass.
- `npm run build --prefix apps/web` -- expected: production build succeeds.
- `git diff --check` -- expected: no whitespace errors.

**Verification Results:**

- Type-check, lint, 14 unit files/50 tests, production build, disposable E2E (4 browser tests), and `git diff --check` passed.
