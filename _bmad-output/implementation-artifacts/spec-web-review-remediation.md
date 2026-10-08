---
title: "Web frontend review remediation"
type: "feature"
created: "2026-10-08"
status: "done"
review_loop_iteration: 1
baseline_commit: "cd65ed2d4516a37ff88494c4608e04a6d71b50cf"
context:
  - "{project-root}/apps/web/vue-frontend-architecture.md"
  - "{project-root}/docs/contracts/auth/registration.md"
  - "{project-root}/docs/contracts/common/http.md"
---

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

**Problem:** The Vue frontend review found confirmed state-integrity bugs in CV/JD editing, stale query-cache writes, inconsistent runtime validation and pagination adapters, and page/shared-layer boundaries that make regressions likely. The highest risk is silently losing or mixing a local draft while navigating, refetching, saving, or creating an immutable Version.

**Approach:** Remediate the confirmed correctness and API-boundary problems first, then consolidate query keys, cache mutation, schemas, and page controllers around the existing Vue 3/TanStack Query architecture. Keep the work in one branch, preserve existing backend contracts and user-visible workflows, and add focused regression tests for the destructive state transitions.

## Boundaries & Constraints

**Always:** Keep server state, saved snapshot, and editable draft distinguishable; never overwrite a dirty draft from a refetch; validate untrusted API/JSON data at the boundary; use QueryClient for server-cache writes; preserve `/api/v1`, cookie credentials, and the repository's `X-CSRF-TOKEN` Sanctum contract; use route constants and semantic tokens where touched.

**Ask First:** Any backend/API contract change, dependency addition, destructive data migration, or behavior change that forces automatic discard of user input.

**Never:** Do not change Laravel code, replace TanStack Query with Pinia, add a second state-management framework, invent optimistic behavior without rollback, or treat architecture suggestions as permission to rewrite unrelated screens.

## I/O & Edge-Case Matrix

| Scenario                    | Input / State                                                       | Expected Output / Behavior                                                                          | Error Handling                                  |
| --------------------------- | ------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------- | ----------------------------------------------- |
| CV dirty refetch            | Editable title/section differs from saved snapshot; query refetches | Draft remains visible and is not replaced by server data                                            | Show recoverable conflict/reconciliation state  |
| CV version creation         | Title, personal information, or active JSON section is dirty        | Version action is blocked with an actionable unsaved-changes message                                | No Version request is sent                      |
| CV partial save             | Title request succeeds and section request fails                    | Saved title/revision is retained; section draft remains editable and error explains partial success | Refresh/cache use the returned server profile   |
| JD route switch             | Route changes from JD A to JD B or `new`                            | Draft, analysis id, pagination selection, and mutation state are scoped/reset to the new resource   | Ignore stale response for the previous resource |
| Invalid CV JSON/API payload | Malformed section text or response shape                            | No mutation is sent; valid data is never committed                                                  | Display validation error and preserve input     |
| Parent resets search        | Parent changes `modelValue` while debounce is pending               | Input reflects the new parent value and emits only subsequent debounced searches                    | No stale search result is emitted               |

</frozen-after-approval>

## Code Map

- `apps/web/src/pages/cv/CvEditorPage.vue` -- current CV draft, title/section save sequencing, conflict reconciliation, and Version action; current personal-info dirty check and refetch overwrite are here.
- `apps/web/src/features/cv/components/CvSectionNav.vue` and `features/cv/types/cv.types.ts` -- section metadata/type union; `experience` exists in the domain type but is absent from navigation.
- `apps/web/src/features/cv/api/cv.api.ts` and `cv.queries.ts` -- CV HTTP boundary, If-Match revision use, and scattered query keys.
- `apps/web/src/pages/jd/JdInputPage.vue` -- JD draft, route-scoped query, analysis/match/delete mutations, and cache invalidation; route changes currently do not reset local state.
- `apps/web/src/features/jd/api/jd.api.ts` and `features/jd/types/jd.types.ts` -- Zod schemas currently end in `as` casts and expose pagination metadata unlike CV versions.
- `apps/web/src/shared/api/client.ts`, `features/auth/api/auth.api.ts`, and `docs/contracts/auth/registration.md` -- CSRF behavior; preserve decoded `XSRF-TOKEN` as `X-CSRF-TOKEN` because it matches the current backend middleware and contract.
- `apps/web/src/shared/components/molecules/SearchInput.vue` -- debounced local input lacks a prop-to-local synchronization watcher.
- `apps/web/src/pages/ai/AiInterviewPage.vue` and `pages/cv/PatchReviewPage.vue` -- direct writes to Vue Query result refs; replace with QueryClient cache updates.
- `apps/web/src/pages/dashboard/DashboardPage.vue`, `pages/match/MatchReportPage.vue`, `pages/templates/TemplatePickerPage.vue`, and `features/*/api` -- page-owned query orchestration and query-key locations to consolidate without changing screen behavior.
- `apps/web/tests/` and `apps/web/tests/e2e/` -- existing Vitest/Playwright regression conventions and end-to-end flows.

## Tasks & Acceptance

**Execution:**

- [x] `features/cv/components/CvSectionNav.vue`, `features/cv/schemas/`, `pages/cv/CvEditorPage.vue`, and focused tests -- add complete typed sections, schema-backed parsing, draft/saved/server separation, dirty guards, conflict-safe refetch, and partial-save handling.
- [x] `shared/components/molecules/SearchInput.vue`, `pages/jd/JdInputPage.vue`, and JD composables/query modules -- synchronize controlled input and scope/reset JD draft, analysis, and mutation state by resource ID.
- [x] `features/jd/types/`, `features/jd/api/`, `features/match/api/`, and shared pagination types -- derive validated types from schemas and normalize actual backend pagination metadata without unsafe casts.
- [x] CV/JD/match/evidence/template query-key and query modules plus affected pages -- centralize keys, replace direct query-data assignments with `setQueryData`/invalidation, and keep cache entries consistent after mutations.
- [x] `pages/dashboard/`, `pages/cv/`, `pages/jd/`, `pages/match/`, `pages/ai/`, and `pages/templates/` -- move non-trivial server-state/workflow orchestration into feature composables while retaining route composition and existing UI behavior.
- [x] `shared/components/molecules/FormField.vue`, shared button usage, `features/templates/components/TemplateCard.vue`, and relevant CSS/docs -- clarify reusable component boundaries, improve field ARIA linkage, replace non-brand hardcoded colors with semantic tokens, and update frontend architecture rules.
- [x] `apps/web/tests/` -- add deferred-response regression coverage for post-submit CV/JD edits, Version conflict recovery, and stale AI/Patch/JD callbacks; then run type-check, lint, unit tests, build, and applicable E2E checks.

**Acceptance Criteria:**

- Given a dirty CV draft, when a query refetch or route response arrives, then the editable draft is preserved and the user receives a clear reconciliation path.
- Given a CV with changes in any section including Personal Info, when dirty state or Version creation is evaluated, then the change is detected and an unsaved Version is never created.
- Given a JD route/resource change, when the new resource loads, then no text, analysis, selection, or mutation key from the previous resource is reused.
- Given a mutation success or failure, when the server response is received, then affected caches reflect the response or remain unchanged with the draft preserved on failure.
- Given malformed JSON or an API payload that violates its schema, when it crosses the boundary, then it is rejected without an unsafe type assertion or partial UI corruption.
- Given a CV or JD mutation is pending, when the user makes a newer edit before the response returns, then the saved snapshot advances while the newer local edit remains dirty and visible.
- Given an AI, Patch, Match, or JD mutation completes after its route resource changes, when its callback runs, then it may update only the originating cache entry and must not mutate or navigate the current screen.
- Given the full web verification commands, when they run with dependencies available, then type-check, lint, unit tests, build, and selected E2E flows pass.

## Design Notes

The Laravel API intentionally exposes separate CV title and section endpoints, so the frontend cannot make that pair transactionally atomic. The implementation must therefore validate the section before any request, apply requests in a deterministic order, and explicitly represent partial success rather than pretending both writes succeeded. Query keys should remain resource-oriented and stable; abstraction is justified only where more than one page needs the same contract.

## Verification

**Commands:**

- `npm run type-check --prefix apps/web` -- expected: Vue/TypeScript compilation succeeds.
- `npm run lint --prefix apps/web` -- expected: ESLint and oxlint succeed.
- `npm run test:unit --prefix apps/web -- --run` -- expected: focused and existing Vitest suites pass.
- `npm run build --prefix apps/web` -- expected: production Vite build succeeds.
- `npm run test:e2e --prefix apps/web -- --workers=1` -- expected: applicable browser flows pass when the API fixture/runtime is available without registration rate-limit contention.

## Verification Results

- `npm run type-check --prefix apps/web` -- passed.
- `npm run lint --prefix apps/web` -- passed.
- `npm run test:unit --prefix apps/web -- --run` -- passed: 14 files, 50 tests.
- `npm run build --prefix apps/web` -- passed: Vite production build completed.
- `./scripts/e1-verify.sh e2e` -- passed: disposable API/database runtime, 50 unit tests, and 4 browser tests.
- `git diff --check` -- passed.

## Suggested Review Order

**State integrity and route-scoped mutations**

- Route pages now render feature editors; controller captures route generation and snapshots.
  [`CvEditor.vue:10`](../../apps/web/src/features/cv/components/CvEditor.vue#L10)

- Draft reconciliation merges server state while preserving only genuinely dirty local fields.
  [`useCvEditorDraft.ts:133`](../../apps/web/src/features/cv/composables/useCvEditorDraft.ts#L133)

- JD page composition delegates route-scoped workflow to one feature controller.
  [`useJdEditorController.ts:19`](../../apps/web/src/features/jd/composables/useJdEditorController.ts#L19)

- AI, Patch, and Match controllers reject stale A-to-B-to-A navigation callbacks.
  [`usePatchReviewController.ts:21`](../../apps/web/src/features/evidence/composables/usePatchReviewController.ts#L21)

**API validation and pagination contracts**

- Auth responses are parsed from unknown before entering application state.
  [`auth.api.ts:12`](../../apps/web/src/features/auth/api/auth.api.ts#L12)

- JD and Match adapters share runtime pagination validation while preserving backend page metadata.
  [`pagination.schemas.ts:3`](../../apps/web/src/shared/schemas/pagination.schemas.ts#L3)

- JD analysis accepts backend-valid string and labelled-object signal items.
  [`jd.schemas.ts:4`](../../apps/web/src/features/jd/schemas/jd.schemas.ts#L4)

**Architecture and accessibility boundaries**

- Dashboard widgets keep resource lists in the eight-column rail and actions in four columns.
  [`DashboardView.vue:42`](../../apps/web/src/features/dashboard/components/DashboardView.vue#L42)

- Form controls receive stable ARIA linkage and native required semantics from one slot contract.
  [`FormField.vue:25`](../../apps/web/src/shared/components/molecules/FormField.vue#L25)

- ESLint prevents route pages bypassing controller and transport boundaries.
  [`eslint.config.ts:47`](../../apps/web/eslint.config.ts#L47)

- Architecture documentation records the runtime boundaries and deliberate color exceptions.
  [`vue-frontend-architecture.md:1274`](../../apps/web/vue-frontend-architecture.md#L1274)

**Verification**

- Regression coverage exercises draft preservation, malformed payloads, and controlled search resets.
  [`web-review-regressions.test.ts:12`](../../apps/web/tests/web-review-regressions.test.ts#L12)

- Route-race tests cover stale AI, Patch, Match, and idempotency callbacks.
  [`evidence-interview-pages.test.ts:131`](../../apps/web/tests/evidence-interview-pages.test.ts#L131)
