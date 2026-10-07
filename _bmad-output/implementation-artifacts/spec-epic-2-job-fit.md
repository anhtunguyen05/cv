---
title: 'Epic 2 - Understand CV Fit for a Job'
type: 'feature'
created: '2026-10-07'
status: 'done'
review_loop_iteration: 0
baseline_commit: '00e9dd0ca12676fb204908ce3f5b64ea052e2974'
context:
  - '/home/tuna2/notthing/cv/_bmad-output/implementation-artifacts/epic-2-context.md'
  - '/home/tuna2/notthing/cv/_bmad-output/planning-artifacts/epics/epic-2-job-fit/contracts.md'
  - '/home/tuna2/notthing/cv/_bmad-output/planning-artifacts/epics/epic-2-job-fit/test-strategy.md'
---

<frozen-after-approval reason="human-owned intent - do not modify unless human renegotiates">

## Intent

**Problem:** The scaffold has no production path for preserving a job description, interpreting its requirements, comparing it with an immutable CV version, or explaining the result. A demo report would be misleading because it cannot prove source identity, ownership, revisions, or deterministic evidence.

**Approach:** Implement the complete Epic 2 vertical slice across Laravel, PostgreSQL, Vue, and tests. Store immutable job-description revisions, run deterministic rule-versioned analysis, create immutable pinned match reports, and expose the lifecycle through authenticated `/api/v1` endpoints and accessible UI states.

## Boundaries & Constraints

**Always:** Enforce authenticated ownership and non-disclosing not-found behavior; preserve decoded raw source and revision history; use `If-Match` for stale writes and UUID-v4 `Idempotency-Key` for mutations/derived work; keep analysis and matching deterministic, versioned, explainable, and proposal-only; use PostgreSQL 16 for integration evidence; keep sensitive source out of ordinary logs; retain loading, empty, validation, authorization, retry, terminal-error, reload, and keyboard-accessible states.

**Ask First:** Any change to the approved route/envelope/error contracts, scoring weights, validation limits, rule vocabulary, persistence semantics, or CV Version contract.

**Never:** Do not add an LLM/provider/worker, ATS qualification or score-boost claim, recruiter ranking, partial derived result, mutable report, direct provider persistence, hardcoded demo report, or a second planning lifecycle.

## I/O & Edge-Case Matrix

| Scenario | Input / State | Expected Output / Behavior | Error Handling |
|----------|--------------|---------------------------|----------------|
| CREATE | Authenticated user, valid raw text, optional metadata, UUID-v4 idempotency key | JD and revision 1 returned; current revision pinned | 422 structured validation; replay returns byte-identical body; foreign identity is never disclosed |
| UPDATE | Current JD, changed source/metadata, matching `If-Match` | Exactly one new current revision; prior revision remains immutable | 409 stale/deleted conflict; 422 no-op; 404-safe foreign lookup |
| ANALYZE | Current revision, supported rule version | Synchronous succeeded analysis with closed signals and provenance | 409 deleted/stale source; retryable 429/503 without partial result; deterministic replay |
| MATCH | Owned immutable CV version plus current successful analysis | Immutable 0-100 report pinned to CV/JD/revision/analysis/rule | 422 analysis required/source conflict; 409 deleted source; idempotent replay |
| REPORT UI | Loading, empty, retryable, terminal, deleted-source historical report | Correct state, focus, labels, source/revision/rule references, semantic evidence groups | Never fall back to a demo or assert ATS/boost outcome |

</frozen-after-approval>

## Code Map

- `apps/api/routes/api.php` -- authenticated `/api/v1` route boundary; add JD, nested analysis, and match-report routes without changing `/api/health`.
- `apps/api/app/Application/Cv/` and `app/Presentation/Http/` -- established transaction, idempotency, request, presenter, and `ApiProblem` patterns to reuse for Epic 2 services/controllers.
- `apps/api/app/Models/` and `database/migrations/2026_09_13_000004..000008_*` -- existing ULID JD/revision/analysis/report substrate; add forward constraints/indexes only where source evidence requires.
- `apps/api/tests/Feature/Cv/` -- authenticated ownership, envelope, conflict, idempotency, and PostgreSQL integration test style; add Epic 2 feature/unit coverage and fixture corpus under `docs/contracts/jd/fixtures/`.
- `apps/web/src/features/jd/`, `src/features/match/`, `src/pages/jd/`, `src/pages/match/`, `src/router/` -- current API/types/pages/router; replace numeric/demo contracts with server-backed lifecycle and accessible state handling.
- `_bmad-output/planning-artifacts/epics/epic-2-job-fit/` -- canonical contracts, lifecycle, UX, decisions, and test gates; implementation must not silently widen them.

## Tasks & Acceptance

**Execution:**
- [x] Implement JD aggregate, immutable revisions, validation, ownership, idempotency, If-Match conflicts, list/detail/edit/delete API, and tests (Story 2.1/2.2).
- [x] Implement deterministic analysis rule 1.0.0, bounded synchronous execution, provenance, retry/error semantics, and tests (Story 2.3).
- [x] Implement deterministic weighted matching against an immutable CV Version, pinned immutable reports, API contracts, and tests (Story 2.4).
- [x] Implement Vue API/query/mutation adapters, create/list/edit/delete/analyze flows, real report loading/error/retry states, and keyboard-accessible evidence presentation (Story 2.1-2.5).
- [x] Add the deterministic fixture corpus and local unit/feature/typecheck/build checks (Story 2.5/test strategy).
- [x] Run the focused PostgreSQL 16 Epic 2 gate.
- [x] Run the expanded PostgreSQL quality corpus and focused browser verification; the Analysis corpus executes 40+12 cases, the Match corpus executes 44+12 cases, and the Epic 2 Playwright journey passes with the migrated CV schema.

**Acceptance Criteria:**
- Given valid authenticated input, when the Epic 2 lifecycle is executed end to end, then the user can create, reload, edit, analyze, match, and review a report whose identifiers and rule versions are source-pinned.
- Given a stale or foreign/deleted resource, when a write or derived operation is attempted, then the API returns the documented structured conflict/not-found response without leaking content or creating partial data.
- Given repeated requests with the same valid idempotency key, when the fingerprint is unchanged, then the response body is byte-identical and no duplicate revision/analysis/report exists.
- Given any report state, when rendered in the web app, then no hardcoded fixture, ATS claim, score-boost promise, or color-only meaning remains and all required recovery/accessibility states are usable.

## Verification

**Commands:**
- `./scripts/e1-verify.sh api-pg` -- expected: the disposable PostgreSQL 16
  stack runs `phpunit.e2e.xml` and the Epic 2 feature/contract tests pass. A
  plain `php artisan test --testsuite=Feature --filter=Epic2` is SQLite-only.
- `php artisan test --testsuite=Unit --filter='(JobDescription|Analysis|Match)'` -- expected: deterministic rule and validation tests pass.
- `npm run type-check && npm run build && npm run test:unit` -- expected: Vue contracts compile, build, and unit tests pass.
- `git diff --check` -- expected: no whitespace errors.

**Implementation evidence (2026-10-07):** Focused Epic 2 PHPUnit and quality
corpus passes on SQLite and a disposable PostgreSQL 16 container. The full API
suite passes on SQLite (`58 tests, 57 passed, 1 skipped, 1,960 assertions`)
and PostgreSQL 16 (`58 tests / 1,962 assertions`). The Analysis corpus executes 40 reviewed plus
12 held-out cases; the Match corpus executes 44 reviewed plus 12 held-out
cases with deterministic replay assertions. The browser suite passes all
three journeys (`register`, `trusted-cv`, and `epic2-job-fit`) after applying
the forward CV/schema migrations in the local API environment. Web unit
coverage is 9 tests across 5 files; CI now runs unit checks and the disposable
Playwright job.

### Review Findings

- [x] [Review][Defer] Persistence-level immutability for revisions, analyses, and Match Reports — application-level no-mutation routes remain the Epic 2 boundary; database/model hardening is deferred to a persistence-hardening task. [apps/api/database/migrations/2026_09_13_000005_create_job_description_revisions_table.php:61]
- [x] [Review][Defer] Shared ATS/AI chrome on report routes — pre-existing copy remains outside Epic 2 and is deferred to a product-copy task. [apps/web/src/shared/components/organisms/AppSidebar.vue:103]
- [x] [Review][Patch] Replay analysis and Match requests before mutable source preconditions — lookup the idempotency receipt before deleted/current-analysis checks so unchanged retries replay the original response. [apps/api/app/Application/JobFit/AnalysisService.php:24]
- [x] [Review][Patch] Reuse mutation idempotency keys across frontend retries — retain one key per mutation attempt and reconcile ambiguous responses instead of generating a new UUID on each retry. [apps/web/src/features/jd/api/jd.api.ts:15]
- [x] [Review][Patch] Enforce the five-second synchronous derivation deadline — return `503 DERIVATION_TEMPORARILY_UNAVAILABLE` without persisting partial analysis or reports. [apps/api/app/Application/JobFit/AnalysisService.php:57]
- [x] [Review][Patch] Preserve closed analysis states and validate schema versions — retain `unknown` seniority/state through persistence/presentation and reject incompatible analysis schema before matching. [apps/api/app/Application/JobFit/AnalysisService.php:57]
- [x] [Review][Patch] Correct analyzer boundary cases — do not map generic “experience” to seniority, return `unknown` for conflicting/negated levels, and canonicalize overlapping soft-skill aliases. [apps/api/app/Application/JobFit/JobDescriptionAnalyzer.php:118]
- [x] [Review][Patch] Make CV evidence evaluation field-aware and token-safe — exclude unsupported sections, match aliases at boundaries, and remove all negated occurrences before awarding evidence or score. [apps/api/app/Application/JobFit/MatchService.php:199]
- [x] [Review][Patch] Validate stored Match Report output before presenting it — enforce report schema, score bounds, required classification fields, and reject corrupt persisted JSON instead of silently normalizing it. [apps/api/app/Application/JobFit/MatchReportPresenter.php:14]
- [x] [Review][Patch] Prevent analysis/match against unsaved JD edits — disable derived actions or save/reconcile the edited source before using the last persisted revision. [apps/web/src/pages/jd/JdInputPage.vue:57]
- [x] [Review][Patch] Key cached analysis by revision and rule version — never surface a prior-rule analysis as current after a rule deployment. [apps/web/src/pages/jd/JdInputPage.vue:36]
- [x] [Review][Patch] Add runtime validation for JD and Analysis API responses and strict report identifiers/score bounds. [apps/web/src/features/jd/api/jd.api.ts:15]
- [x] [Review][Patch] Complete report source identity and historical labeling — render Job Description ID/role/company and Analysis ID, and distinguish an older pinned revision from the current source. [apps/web/src/pages/match/MatchReportPage.vue:65]
- [x] [Review][Patch] Restore semantic evidence ordering and headings — expose missing required, weak required, missing preferred, and matched groups with semantic list/heading structure. [apps/web/src/features/match/components/SkillGapList.vue:18]
- [x] [Review][Patch] Complete validation and recovery states — associate field errors/focus, provide retry/reload for match and delete conflicts, and invalidate active JD caches after deletion. [apps/web/src/pages/jd/JdInputPage.vue:125]
- [x] [Review][Patch] Align Unicode limits with server code-point validation — replace native UTF-16 `maxlength` behavior with code-point-safe input enforcement. [apps/web/src/pages/jd/JdInputPage.vue:88]
- [x] [Review][Patch] Make all paginated resources reachable in the UI — paginate CV Version selection and provide navigation/view-all for saved Job Descriptions beyond the dashboard preview. [apps/web/src/pages/dashboard/DashboardPage.vue:281]
- [x] [Review][Patch] Expand API verification for ownership and mutation boundaries — add foreign analysis/report reads, mixed-owner derivation attempts, analysis/update/delete idempotency replay/conflict, stale DELETE, throttling, and no-partial-state assertions. [apps/api/tests/Feature/JobFit/Epic2JobFitTest.php:85]
- [x] [Review][Patch] Expand deterministic corpus coverage to persisted API contracts and optional scoring — exercise role/domain/seniority contributions, exact report shape, and HTTP/presenter projections. [apps/api/tests/Feature/JobFit/MatchQualityCorpusTest.php:91]
- [x] [Review][Patch] Expand browser coverage for source pinning and UI states — assert analysis/report IDs, historical immutability after source edits, revision-analysis reset, evidence filters, pagination, and retry/error paths. [apps/web/tests/e2e/epic2-job-fit.spec.ts:41]
- [x] [Review][Patch] Serialize analysis and Match idempotency receipt lookup after the owned Job Description lock so concurrent retries cannot create duplicate derived rows. [apps/api/app/Application/JobFit/AnalysisService.php:36]
- [x] [Review][Patch] Validate persisted Analysis envelopes and metadata encoding, preserve `unknown` for unsupported substantive requirements, and guard zero-evidence reports from division by zero. [apps/api/app/Application/JobFit/AnalysisPresenter.php:14]
- [x] [Review][Patch] Tighten frontend JD/Analysis runtime contracts to ULIDs and the pinned `1.0.0` rule/schema versions. [apps/web/src/features/jd/api/jd.api.ts:18]
- [x] [Review][Patch] Keep preferred weak evidence visible, expose report recommendation signal references, and retain dashboard/report-list loading, retry, and pagination recovery states. [apps/web/src/features/match/components/SkillGapList.vue:18]
- [x] [Review][Patch] Reset idempotency keys when a new mutation payload is edited, add save/delete retry actions, and reject overflowing pagination values. [apps/web/src/pages/jd/JdInputPage.vue:170]
- [x] [Review][Patch] Fix the Epic 2 contract migration rollback closure capture and add route-level tests for fresh-key analysis reuse, deleted-list exclusion, corrupt-report rejection, mutation throttling, pagination totals, and CV Version snapshot reproducibility. [apps/api/database/migrations/2026_10_07_000011_add_epic_two_contract_columns.php:38]

## Suggested Review Order

**Lifecycle and source integrity**

- Lock the owned Job Description before checking idempotency to serialize derived work.
  [`AnalysisService.php:28`](../../apps/api/app/Application/JobFit/AnalysisService.php#L28)

- Preserve revision identity and stale-write semantics across create, update, and delete.
  [`JobDescriptionService.php:68`](../../apps/api/app/Application/JobFit/JobDescriptionService.php#L68)

- Pin CV, JD revision, Analysis, rule versions, and historical source status in reports.
  [`MatchService.php:67`](../../apps/api/app/Application/JobFit/MatchService.php#L67)

**Contract and corruption boundaries**

- Reject incompatible persisted report payloads before they reach API or UI consumers.
  [`MatchReportPresenter.php:13`](../../apps/api/app/Application/JobFit/MatchReportPresenter.php#L13)

- Validate persisted Analysis versions, states, identifiers, and signal shapes.
  [`AnalysisPresenter.php:12`](../../apps/api/app/Application/JobFit/AnalysisPresenter.php#L12)

- Enforce source limits, UTF-8 metadata, and deterministic unknown-versus-absent behavior.
  [`JobDescriptionValidator.php:58`](../../apps/api/app/Application/JobFit/JobDescriptionValidator.php#L58)

**Accessible browser flow**

- Keep mutation retries safe while associating validation errors with focused controls.
  [`JdInputPage.vue:115`](../../apps/web/src/pages/jd/JdInputPage.vue#L115)

- Expose pinned source identifiers, historical labels, evidence groups, and recommendation links.
  [`MatchReportPage.vue:65`](../../apps/web/src/pages/match/MatchReportPage.vue#L65)

- Exercise the complete save, analyze, match, edit, delete, filter, pagination, and retry journey.
  [`epic2-job-fit.spec.ts:3`](../../apps/web/tests/e2e/epic2-job-fit.spec.ts#L3)

**Supporting verification**

- Cover preferred weak evidence rendering in a focused component test.
  [`skill-gap-list.test.ts:5`](../../apps/web/tests/skill-gap-list.test.ts#L5)

- Review PostgreSQL/SQLite corpus, API ownership, throttling, replay, corruption, and snapshot tests.
  [`Epic2JobFitTest.php:298`](../../apps/api/tests/Feature/JobFit/Epic2JobFitTest.php#L298)
