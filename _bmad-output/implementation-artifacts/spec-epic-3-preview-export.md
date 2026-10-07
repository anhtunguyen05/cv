---
title: 'Epic 3 - Preview and Export an Application-Ready CV'
type: 'feature'
created: '2026-10-07'
status: 'done'
baseline_commit: '5e8d3eada44cd329405f3bee8e7dabbcb23433af'
review_loop_iteration: 0
context:
  - '{project-root}/_bmad-output/implementation-artifacts/epic-3-context.md'
  - '{project-root}/_bmad-output/planning-artifacts/epics/epic-3-preview-export/contracts.md'
  - '{project-root}/_bmad-output/planning-artifacts/epics/epic-3-preview-export/decisions.md'
  - '{project-root}/docs/contracts/cv/version-v1.md'
  - '{project-root}/docs/frontend-architecture.md'
---

<frozen-after-approval reason="human-owned intent - do not modify unless human renegotiates">

## Intent

**Problem:** The template page is still fixture-driven, Preview can read a mutable Profile instead of an immutable Version, and print controls currently claim an export path without a trusted template or readiness contract. Laravel has a template table but no catalog or Version-preview boundary.

**Approach:** Implement Epic 3 as one vertical slice: expose active compatible Template pairs, resolve an owned immutable CV Version with an exact template tuple, render all supported sections through one safe deterministic Vue projection, and invoke browser print only after the reviewed preview is ready.

## Boundaries & Constraints

**Always:** Keep Laravel authoritative under `/api/v1`, use `{data}` and structured API errors, enforce authenticated ownership and non-disclosing foreign-resource responses, use the stored Version snapshot only, identify templates by stable `(id, version)`, allow only active compatible pairs, render text inertly, allow only approved `http`, `https`, and `mailto` links, and preserve loading, empty, unavailable, stale, retry, and renderer-failure states. Keep browser controls out of print output and use bundled/local assets only.

**Ask First:** Halt if implementation requires changing the approved API routes/envelopes, immutable Version contract, template identity semantics, adding a runtime dependency, server PDF/storage/audit behavior, or changing an applied migration instead of adding a forward migration.

**Never:** Do not reconstruct a Version from a live Profile, allow providers/workers/AI to write or render the result, expose foreign CV data, accept arbitrary HTML/CSS renderer configuration, claim that browser dialog completion was observed, or create a server export artifact in this MVP.

## I/O & Edge-Case Matrix

| Scenario | Input / State | Expected Output / Behavior | Error Handling |
|----------|--------------|---------------------------|----------------|
| TEMPLATE_CATALOG | Authenticated session; active, inactive, incompatible, or empty registry | `{data: TemplateSummary[]}` ordered by name then id; only selectable compatible pairs appear | 401 for guest; empty state is explicit; no-store response |
| PREVIEW | Owned immutable Version plus `template_id` and `template_version` | Exact stored snapshot rendered with the requested immutable template pair and source identifiers | 404 `CV_VERSION_NOT_FOUND`; 409 `TEMPLATE_UNAVAILABLE`; reject malformed identifiers without disclosure |
| RENDERING | Empty optional sections, Unicode, markup-like text, unsafe/long links, long content | Approved section order, inert text, safe links, no empty headings/fragments, deterministic wrapping/page breaks | Fail closed with retryable renderer state; never silently fall back to live Profile data |
| EXPORT | Ready exact preview, blocked/unsupported print, user cancels dialog | A4 browser print/HTML with title `{version_name} - {template_name}`, hidden controls, preserved route context | Guard until ready; explain unsupported/blocked print; dialog result remains unknown and no success claim is persisted |

</frozen-after-approval>

## Code Map

- `apps/api/routes/api.php`, `app/Application/Cv/`, `app/Presentation/Http/Controllers/Cv/`, `app/Models/`, and `database/migrations/` -- add the authenticated Template catalog and exact Version-preview projection while reusing `VersionService::findOwned`, `ApiResponse`, `ApiProblem`, ULID validation, and forward-only schema changes.
- `apps/api/tests/Feature/Cv/` -- add catalog, availability, ownership/non-disclosure, snapshot immutability, tuple revalidation, and error-envelope coverage using existing PostgreSQL-oriented feature patterns.
- `apps/web/src/features/templates/{api,types,components}/`, `src/features/cv/{api,components}/`, and `src/pages/templates/` -- replace numeric fixtures with server contracts, preserve Version context, and select an active template by id/version.
- `apps/web/src/pages/cv/CvVersionPage.vue`, `src/app/router/index.ts`, `src/app/layouts/PreviewLayout.vue`, and `src/assets/main.css` -- add URL-addressable exact preview, guarded browser print/export, semantic screen/print regions, A4 print rules, and no remote asset dependency.
- `apps/web/tests/`, `vitest.config.ts`, and `playwright.config.ts` -- cover renderer edge cases, accessibility semantics, route/query states, print emulation, and the critical saved-Version journey.

## Tasks & Acceptance

**Execution:**
- [x] Add the Template model/read service/presenter/controller, compatibility and availability query, seed/forward migration alignment, route, and focused API tests.
- [x] Add the exact Version-preview service/controller and contract tests; render only the owned stored snapshot and revalidate the requested template pair.
- [x] Replace web template and Version pages/API types/queries with the approved envelopes, dynamic routes, loading/error/empty states, and selection return path.
- [x] Refactor `CvPreviewDocument` into the shared v1.0.0 section registry with safe link handling, inert text, deterministic order, and empty-section omission.
- [x] Implement guarded `PreviewLayout` print/export behavior, print title/margins/controls, and screen/print CSS without server artifacts.
- [x] Add focused Vue tests, API tests, type-check/lint/build validation, and a Playwright journey for select → preview → print readiness and source pinning.

**Acceptance Criteria:**
- Given an authenticated user with a saved Version, when a compatible active Template is selected, then the catalog and preview use the same stable `(id, version)` and the UI URL preserves the Version context.
- Given a later Profile edit, when the same Version is previewed or printed, then the output remains byte-equivalent to its stored snapshot and never shows unsaved Profile values.
- Given foreign, missing, inactive, stale, incompatible, malformed, or unsafe input, when the catalog or preview is requested, then the documented non-disclosing error/state is shown without partial rendering.
- Given a ready preview, when the user chooses Export/Print, then the browser print path uses the exact reviewed source, A4 portrait styling, correct title, hidden controls, accessible fallback messaging, and no server-created PDF or export record.

## Spec Change Log

## Verification

**Commands:**
- `php artisan test --compact tests/Feature/Cv` -- expected: Epic 3 catalog, preview, ownership, and contract tests pass.
- `vendor/bin/pint --dirty --format agent` -- expected: modified PHP follows repository formatting.
- `npm --prefix apps/web run type-check && npm --prefix apps/web run lint:check && npm --prefix apps/web run test:unit && npm --prefix apps/web run build` -- expected: web contracts, tests, lint, and production build pass.
- `npm --prefix apps/web run test:e2e -- --project=chromium` -- expected: saved-Version template/preview/print-readiness journey passes when the disposable API/web harness is available.
- `git diff --check` -- expected: no whitespace errors.

**Evidence:**

- PostgreSQL harness: 64 tests, 1,998 assertions passed, including catalog filtering, immutable snapshot, ownership, unsafe-link, and error-envelope cases.
- PHP formatting: explicit Epic 3 Pint invocation passed; `--dirty` is unavailable in the disposable container because it is not a Git worktree.
- Web validation: type-check, ESLint/Oxlint, 11 unit tests, production build, and changed-file Prettier checks passed.
- Chromium harness: registration, trusted Version, and Epic 3 select → preview → print-readiness journeys passed; browser dialog save/cancel remains intentionally unknown.
- The same full harness still reports one pre-existing Epic 2 Match Report failure; it is outside Epic 3 files and does not affect the Epic 3 journey.

## Suggested Review Order

**API boundary and persistence**

- Start with the exact preview route and structured source validation.
  [`PreviewController.php:15`](../../apps/api/app/Presentation/Http/Controllers/Cv/PreviewController.php#L15)

- Trace owned snapshot projection, schema guards, safe links, and section omission.
  [`PreviewService.php:10`](../../apps/api/app/Application/Cv/PreviewService.php#L10)

- Review catalog availability and application-owned renderer section registry.
  [`TemplateService.php:10`](../../apps/api/app/Application/Cv/TemplateService.php#L10)

- Verify immutable template pairs and browser-only export lifecycle reconciliation.
  [`2026_10_07_000013_align_epic_three_templates.php:38`](../../apps/api/database/migrations/2026_10_07_000013_align_epic_three_templates.php#L38)

**Preview and print surface**

- Follow readiness, duplicate-source rejection, renderer failure, and revalidation gating.
  [`CvVersionPage.vue:25`](../../apps/web/src/pages/cv/CvVersionPage.vue#L25)

- Inspect print preparation, title hint, timeout, stale-intent guard, and unknown outcome.
  [`PreviewLayout.vue:21`](../../apps/web/src/app/layouts/PreviewLayout.vue#L21)

- Check inert text, safe links, incoming section order, wrapping, and empty omission.
  [`CvPreviewDocument.vue:35`](../../apps/web/src/features/cv/components/CvPreviewDocument.vue#L35)

**User flow and verification**

- Review explicit Version selection and retryable catalog/loading/empty states.
  [`TemplatePickerPage.vue:83`](../../apps/web/src/pages/templates/TemplatePickerPage.vue#L83)

- Validate catalog, ownership, snapshot immutability, and unsafe-source regression coverage.
  [`Epic3PreviewTest.php:17`](../../apps/api/tests/Feature/Cv/Epic3PreviewTest.php#L17)

- Run the saved-Version browser journey through selection, exact preview, and print readiness.
  [`epic3-preview-export.spec.ts:3`](../../apps/web/tests/e2e/epic3-preview-export.spec.ts#L3)
