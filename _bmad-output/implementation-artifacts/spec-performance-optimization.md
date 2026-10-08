---
title: 'End-to-end performance optimization for CV and dashboard workflows'
type: 'refactor'
created: '2026-10-08'
status: 'in-progress'
baseline_commit: '96a02306cf0d28b970d51bcf0bf16f9e4107a2dd'
review_loop_iteration: 0
context:
  - 'D:/CareerFitCV/AGENTS.md'
  - 'D:/CareerFitCV/apps/api/AGENTS.md'
  - 'D:/CareerFitCV/docs/performance-optimization.txt'
---

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

**Problem:** Collection APIs and editor hot paths process substantially more data than their screens need. The dashboard fetches full CV/report objects, version lists retain immutable snapshots, CV completion clones/parses the full document, and JD input repeats expensive Unicode/byte work. A global cache policy also causes avoidable requests and memory retention.

**Approach:** Ship one reviewable PR that establishes a repeatable performance baseline, changes list contracts to summary projections with real pagination/count aggregation, reduces editor input work without weakening validation or concurrency, and applies resource-specific query/cache behavior. Rendering and bundle changes are evidence-gated by the baseline.

## Boundaries & Constraints

**Always:** Keep Laravel authoritative under `/api/v1`; preserve ownership isolation, Zod validation at API boundaries, immutable CV Version snapshots, `If-Match` conflict handling, `Idempotency-Key` semantics, accessible loading/error states, and existing detail response behavior.

**Ask First:** Any new dependency, database schema change, public contract version outside the coordinated `/api/v1` client, or measured optimization that requires changing product behavior.

**Never:** Do not remove validation, move server state to Pinia, change the frontend architecture, add SSR/Web Workers/virtualization, or lazy-load components without profiler/bundle evidence. Do not claim milliseconds or percentage gains before benchmark results.

## I/O & Edge-Case Matrix

| Scenario | Input / State | Expected Output / Behavior | Error Handling |
|----------|--------------|---------------------------|----------------|
| Summary collection | Authenticated user with large CVs/versions | List endpoints return metadata-only summaries and complete pagination metadata | Preserve existing validation and ownership errors |
| Detail retrieval | User opens a CV Profile or CV Version | Detail endpoint returns the full document/snapshot needed by editor/preview | Foreign or invalid IDs remain non-disclosing |
| Dashboard counts | Dashboard loads with many Match Reports | One scoped summary response returns CV/JD/report counts without report objects | Existing retry/error state remains visible |
| Invalid active draft | CV section contains malformed JSON while editing | Completion uses last valid committed section; draft text remains editable | No exception escapes the computed state |
| Unicode JD input | Input contains surrogate pairs/combining characters near limits | Client truncates/counts by code point and preserves valid Unicode | Backend remains authoritative for final limits |

</frozen-after-approval>

## Code Map

- `apps/api/app/Presentation/Http/Controllers/Cv/ProfileController.php` and `VersionController.php` -- paginated collection boundaries; currently map every item through full presenters.
- `apps/api/app/Application/Cv/ProfilePresenter.php` and `VersionPresenter.php` -- split summary/detail projections while leaving detail callers unchanged.
- `apps/api/app/Presentation/Http/Controllers/JobFit/MatchReportController.php` and dashboard routes/services -- add an authenticated scoped count summary without serializing reports.
- `apps/web/src/features/cv/api/cv.api.ts`, `cv.queries.ts`, `cv.keys.ts`, `cv.schemas.ts`, `cv.types.ts` -- summary DTOs, page-aware queries, and boundary parsing.
- `apps/web/src/features/dashboard/composables/useDashboardController.ts` -- consume summary counts and profile pages; stop querying report list for KPI values.
- `apps/web/src/features/cv/composables/useCvEditorDraft.ts` -- replace full-document completion capture with section-local committed/draft checks.
- `apps/web/src/features/jd/composables/useJdEditorController.ts` -- make code-point and byte-limit checks fast on normal keystrokes while preserving limits.
- `apps/web/src/features/templates/composables/useTemplatePickerController.ts`, `templates.queries.ts`, and `TemplateCard.vue` -- conditional queries and profiler-gated spotlight changes.
- `apps/web/src/app/providers/vue-query.ts` and mutation composables -- resource-specific stale/gc policy and targeted invalidation.
- `apps/api/tests/Feature/Cv/ProfileVersionTest.php`, JobFit tests, and `apps/web/tests/*regressions*.test.ts` -- existing contract/regression coverage to extend.

## Tasks & Acceptance

**Execution:**
- [x] Add baseline fixture/scenario documentation and before-change static measurements; keep the first budget report-only.
- [x] Add summary presenters/contracts, dashboard summary counts, and complete page-aware API/frontend consumers; update feature tests.
- [x] Optimize CV completion and JD input hot paths; add malformed-draft, Unicode, dirty-state, and conflict regressions.
- [x] Apply resource-specific Vue Query policies, conditional template fetching, and targeted mutation invalidation; test focus/remount behavior.
- [x] Run bundle/render evidence review and include no TemplateCard or layout changes without profiler proof.
- [ ] Run API tests, web tests, type-check, lint, build, and diff checks; record before/after measurements in the PR. (Web/static checks pass; API runtime is unavailable because PHP/Docker is not running.)

**Acceptance Criteria:**
- Given a collection request, when it returns, then list payloads contain no full CV document or immutable snapshot unless a detail endpoint was requested.
- Given more than one page of profiles or versions, when the user navigates pages, then every page is addressable and metadata remains correct.
- Given a dashboard load, when counts are displayed, then no Match Report object is fetched solely to calculate a count.
- Given an invalid active CV draft, when completion recalculates, then committed completion remains correct and editing does not throw.
- Given Unicode JD input, when the user types near limits, then code-point behavior remains correct without repeated full-string work on ordinary input.
- Given a mutation or query lifecycle event, when cache policy applies, then only the allowed resource queries refetch and no protected or immutable data becomes stale incorrectly.
- Given baseline and after measurements, when the PR is reviewed, then every claimed gain has reproducible median/p95 evidence and all existing correctness invariants pass.

## Design Notes

Collection response changes are coordinated with the first-party frontend in this PR; detail endpoints remain the source of full documents and snapshots. Dashboard counts are computed server-side under the authenticated user scope. Baseline and profiler artifacts are evidence, not production data, and rendering/bundle work is omitted if it does not show a measurable bottleneck.

## Verification

**Commands:**
- `cd apps/api; php artisan test --compact` -- expected: API contract, authorization, pagination, and summary tests pass.
- `cd apps/web; npm run test:unit` -- expected: summary schemas, editor, cache, dashboard, and Unicode regressions pass.
- `cd apps/web; npm run type-check` -- expected: no TypeScript errors.
- `cd apps/web; npm run lint` -- expected: ESLint and oxlint pass.
- `cd apps/web; npm run build` -- expected: production bundle succeeds.
- `git -c safe.directory=D:/CareerFitCV diff --check` -- expected: no whitespace errors.
