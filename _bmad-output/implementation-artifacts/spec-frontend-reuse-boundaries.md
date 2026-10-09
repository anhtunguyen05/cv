---
title: 'Refactor frontend reuse boundaries'
type: 'refactor'
created: '2026-10-09'
status: 'done'
baseline_commit: '532fd5cd4ee21fa7608f8fd4b55689d4d1ed29be'
review_loop_iteration: 0
context:
  - '{project-root}/apps/web/vue-frontend-architecture.md'

---

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

**Problem:** The web frontend has several flat controller contracts, repeated pagination logic and markup, a route page that still owns feature orchestration, and an unused `useAuth` facade. These patterns make child components depend on unrelated state and make small behavior changes spread across multiple screens.

**Approach:** Introduce narrow, consumer-backed reuse boundaries while preserving existing API contracts, route behavior, query ownership, auth side effects, and visible UI. Group controller outputs by capability, centralize page navigation and pagination presentation, extract the shared CV Version selection surface, and make the Template route a thin feature wrapper.

## Boundaries & Constraints

**Always:** Keep TanStack Query/API orchestration in feature controllers/components; preserve route paths, auth CSRF/session behavior, redirects, cache invalidation, stale-response guards, pagination metadata, and current copy/UX. Reuse only where at least two current consumers exist.

**Ask First:** Any backend/API contract change, new dependency, auth behavior change, automatic draft discard, or visual redesign.

**Never:** Do not create a generic all-purpose async/list component, replace Pinia or Vue Query, move server state into shared components, change retry policy without an explicit decision, or edit backend/planning artifacts other than this implementation spec.

## I/O & Edge-Case Matrix

| Scenario | Input / State | Expected Output / Behavior | Error Handling |
|----------|--------------|---------------------------|----------------|
| Pager boundary | First or last page | Previous/Next is disabled and page remains within `[1, lastPage]` | Treat missing/zero metadata as one page |
| Pager refresh | `lastPage` changes while a query is fetching | Do not clamp during fetch; reconcile after stable data arrives | Preserve the active request and avoid duplicate navigation |
| Dashboard child | Profiles, JD list, or summary state changes | Only the owning child contract changes | No unrelated controller fields leak into props |
| Auth consumer | Login/register/logout form mounts | Existing mutation behavior and side effects remain unchanged | Existing errors remain exposed by the mutation |

</frozen-after-approval>

## Code Map

- `apps/web/src/shared/composables/usePagination.ts` -- currently unused total/per-page helper; replace with a query-metadata-aware page-navigation contract.
- `apps/web/src/shared/components/` -- location for a presentation-only `PaginationNav.vue`; it must not import feature queries.
- `apps/web/src/features/dashboard/composables/useDashboardController.ts` -- group profile, JD, and summary state; preserve existing query functions and counts.
- `apps/web/src/features/dashboard/components/DashboardStats.vue`, `CvProfileList.vue`, `JdList.vue` -- change props from the full dashboard controller to narrow models.
- `apps/web/src/features/jd/composables/useJdEditorController.ts` -- retain workflow orchestration but expose grouped form, analysis, match, and page contracts.
- `apps/web/src/features/jd/components/JdForm.vue`, `JdAnalysisPanel.vue`, `JdMatchCreator.vue` -- consume only their grouped contracts.
- `apps/web/src/features/cv/composables/useCvEditorController.ts` and `CvEditor.vue` -- group editor/profile/version outputs without splitting the save workflow.
- `apps/web/src/features/cv/components/CvPreview.vue` and `apps/web/src/pages/templates/TemplatePickerPage.vue` -- share current CV Version selection/pagination presentation through a feature component.
- `apps/web/src/features/templates/components/TemplatePicker.vue` -- new feature screen; move page-owned controller/template markup here.
- `apps/web/src/pages/templates/TemplatePickerPage.vue` -- retain only feature import and render.
- `apps/web/src/features/auth/composables/useAuth.ts` and `features/auth/index.ts` -- remove the unused facade only after repository-wide consumer search; keep mutation hooks and store behavior.
- `apps/web/tests/web-architecture-boundaries.test.ts` and new focused tests -- protect wrappers, narrow contracts, pager boundaries, and auth behavior.

## Tasks & Acceptance

**Execution:**

- [x] `apps/web/src/shared/composables/usePageNavigation.ts`, `PaginationNav.vue`, and focused tests -- add bounded page state and reusable pager presentation without owning query data.
- [x] Dashboard controller and three dashboard widgets -- group contracts and migrate all pagination to the shared logic.
- [x] JD editor controller and three JD children -- expose and consume narrow grouped contracts.
- [x] CV editor controller/screen, CV Version selection surfaces, and template feature screen -- group CV editor state, reuse version selection/pagination, and thin the route page.
- [x] Auth facade and public exports -- remove only the unused `useAuth` layer while preserving mutation hooks and existing tests.
- [x] Architecture/regression tests -- assert the new boundaries and run the complete frontend verification suite.

**Acceptance Criteria:**

- Given a dashboard child renders, when an unrelated capability changes, then its prop contract and behavior remain unchanged.
- Given any paginated current consumer, when the page changes or metadata updates, then navigation stays bounded and does not clamp during an active fetch.
- Given CV Version selection appears in preview and template flows, when shared presentation changes, then both consumers use the same feature component while retaining their own destination/action.
- Given the template route loads, when its page module is inspected, then it only mounts the feature screen and owns no query/controller orchestration.
- Given auth forms mount and submit, when login, registration, or logout completes or fails, then the existing store, redirect, reconciliation, cache, and error behavior is unchanged.
- Given frontend verification runs, when dependencies are available, then type-check, lint, unit tests, build, applicable E2E, and diff checks pass.

## Design Notes

The JD/CV controllers contain real route-scoped workflows and should not be split into generic services. The reusable seam is the data contract consumed by each child. Query functions remain feature-owned; only page navigation state and presentation are shared.

## Verification

**Commands:**

- `npm run type-check --prefix apps/web` -- expected: Vue/TypeScript compilation succeeds.
- `npm run lint --prefix apps/web` -- expected: ESLint and Oxlint succeed.
- `npm run test:unit --prefix apps/web -- --run` -- expected: all focused and existing Vitest suites pass.
- `npm run build --prefix apps/web` -- expected: production Vite build succeeds.
- `./scripts/e1-verify.sh e2e` -- expected: applicable disposable E2E checks pass when runtime access is available.
- `git diff --check` -- expected: no whitespace errors.

## Verification Results

- `npm run type-check --prefix apps/web` passed.
- `npm run lint --prefix apps/web` passed.
- `npm run test:unit --prefix apps/web -- --run` passed: 21 files, 78 tests.
- `npm run build --prefix apps/web` passed.
- `./node_modules/.bin/prettier --check src/` passed.
- `git diff --check` passed.
- `./scripts/e1-verify.sh e2e` was blocked by Docker API permission in the current environment (`/var/run/docker.sock`).

## Suggested Review Order

**Controller capability boundaries**

- Dashboard state is grouped by owning capability before reaching child widgets.
  [`useDashboardController.ts:7`](../../apps/web/src/features/dashboard/composables/useDashboardController.ts#L7)

- JD orchestration remains route-scoped while exposing focused form, analysis, match, and page contracts.
  [`useJdEditorController.ts:407`](../../apps/web/src/features/jd/composables/useJdEditorController.ts#L407)

- CV save/version workflows stay together while editor consumers receive grouped state.
  [`useCvEditorController.ts:281`](../../apps/web/src/features/cv/composables/useCvEditorController.ts#L281)

- Feature screens pass each child only its capability contract.
  [`DashboardView.vue:41`](../../apps/web/src/features/dashboard/components/DashboardView.vue#L41)

- JD presentation consumes grouped contracts without owning query orchestration.
  [`JdEditor.vue:44`](../../apps/web/src/features/jd/components/JdEditor.vue#L44)

**Reusable pagination and CV Version surfaces**

- Page state normalizes metadata and reconciles only after active requests settle.
  [`usePageNavigation.ts:16`](../../apps/web/src/shared/composables/usePageNavigation.ts#L16)

- Shared pagination presentation preserves button and legacy text-link appearances.
  [`PaginationNav.vue:4`](../../apps/web/src/shared/components/PaginationNav.vue#L4)

- CV Version selection centralizes preview/card markup while callers retain destinations.
  [`CvVersionSelector.vue:7`](../../apps/web/src/features/cv/components/CvVersionSelector.vue#L7)

- Preview flow supplies its own paged versions query and destination.
  [`CvPreview.vue:10`](../../apps/web/src/features/cv/components/CvPreview.vue#L10)

- JD matching keeps its feature-specific labels and text-link pager appearance.
  [`JdMatchCreator.vue:49`](../../apps/web/src/features/jd/components/JdMatchCreator.vue#L49)

**Route and auth boundaries**

- Template route is now a composition-only wrapper.
  [`TemplatePickerPage.vue:1`](../../apps/web/src/pages/templates/TemplatePickerPage.vue#L1)

- Template workflow owns its controller and reuses the public CV feature boundary.
  [`TemplatePicker.vue:1`](../../apps/web/src/features/templates/components/TemplatePicker.vue#L1)

- Auth public exports retain store and mutation hooks after removing the unused facade.
  [`index.ts:1`](../../apps/web/src/features/auth/index.ts#L1)

**Supporting verification**

- Composable tests cover page bounds, metadata normalization, and fetch reconciliation.
  [`frontend-reuse-boundaries.test.ts:5`](../../apps/web/tests/frontend-reuse-boundaries.test.ts#L5)

- Component tests cover pager appearances and both CV Version selector variants.
  [`pagination-components.test.ts:28`](../../apps/web/tests/pagination-components.test.ts#L28)

- Architecture tests protect thin routes, public imports, and capability props.
  [`web-architecture-boundaries.test.ts:15`](../../apps/web/tests/web-architecture-boundaries.test.ts#L15)
