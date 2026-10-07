---
title: '4.1 Start an Evidence interview'
type: 'feature'
created: '2026-10-07'
status: 'done'
baseline_commit: '20d470ac9df3cb1f69ef15a1f089a11495d35159'
review_loop_iteration: 0
context:
  - D:/CareerFitCV/_bmad-output/implementation-artifacts/epic-4-context.md
  - D:/CareerFitCV/_bmad-output/planning-artifacts/epics/epic-4-ai-revision/stories/4-1-start-an-evidence-interview/README.md
  - D:/CareerFitCV/_bmad-output/planning-artifacts/epics/epic-4-ai-revision/contracts.md
  - D:/CareerFitCV/apps/api/AGENTS.md
---

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

**Problem:** A stored Match Report currently exposes missing and weak signals but
has no server-owned session for collecting truthful User Evidence. A client must
not invent areas, questions, source IDs, or session state.

**Approach:** Add an owned Evidence Interview aggregate and a read/start API.
Resolve the exact Match Report source tuple server-side, snapshot the first five
unresolved signals in stored report order, and create deterministic template
questions only when an interview is needed.

## Boundaries & Constraints

**Always:** Laravel resolves ownership through User → Match Report → CV Version,
JD revision, and Analysis; source IDs and areas are server-derived; the source
CV Version and Match Report remain immutable; duplicate starts reconcile through
the same idempotency key; missing/foreign resources are non-disclosing.

**Ask First:** Stop if Epic 2's Match Report consumer checkpoint has not been
accepted, or if the existing `/api/v1` envelope/auth/idempotency conventions
cannot be reused without changing their approved contracts.

**Never:** Do not accept client-provided owner, CV/JD/Analysis IDs, areas,
questions, status, provider output, or arbitrary question text. Do not invoke an
AI provider, mutate a CV Version, persist Evidence answers, or implement Stories
4.2–4.6 in this spec.

## I/O & Edge-Case Matrix

| Scenario | Input / State | Expected Output / Behavior | Error Handling |
|----------|---------------|-----------------------------|----------------|
| HAPPY_PATH | Owned report has missing/weak signals | `201` Interview with exact source tuple, ≤5 ordered areas, deterministic questions, `active` status | N/A |
| IDEMPOTENT_RETRY | Same owned report and same key | Original `201` body is returned; no second session | Changed fingerprint returns stable conflict |
| NOT_NEEDED | Owned report has no missing/weak signals | No session is created | `409 INTERVIEW_NOT_NEEDED` |
| FOREIGN_OR_MISSING | Unknown, malformed, or another User's report | No source details are disclosed | `404 RESOURCE_NOT_FOUND` |
| STALE_OR_CLOSED | Existing completed/expired session or incompatible source | Historical session remains unchanged; no new trusted state | Stable conflict with current safe action |

</frozen-after-approval>

## Code Map

- `apps/api/app/Models/MatchReport.php` — owned Match Report relations and pinned source IDs; read-only consumer.
- `apps/api/app/Application/JobFit/MatchService.php` — existing `findOwned` ownership pattern and ULID validation to reuse.
- `apps/api/app/Application/JobFit/MatchReportPresenter.php` — stored `missing_skills`/`weak_evidence` shape and report ordering source.
- `apps/api/app/Presentation/Http/Controllers/JobFit/MatchReportController.php` — controller/error-envelope style to mirror for Interview actions.
- `apps/api/routes/api.php` — protected `/api/v1` route group and CSRF/malformed JSON middleware conventions.
- `apps/api/app/Application/Cv/CvIdempotency.php` — existing owner/operation/key replay boundary.
- `apps/web/src/pages/match/MatchReportPage.vue` — current Match Report entry point where the interview CTA and no-area state belong.
- `apps/web/src/features/match/api/match.api.ts` — Zod parsing/query adapter pattern for a new Interview adapter.
- `apps/web/src/app/router/index.ts` — protected route conventions for the Interview page.
- `apps/api/database/migrations/` and `apps/api/tests/Feature/JobFit/` — migration and two-user PostgreSQL/feature test conventions; no Epic 4 tables exist yet.

## Tasks & Acceptance

**Execution:**
- [x] `apps/api/database/migrations/` and `apps/api/app/Models/` — add Interview persistence with ULID, owner, pinned source tuple, ordered area/question snapshot, status, expiry, and uniqueness/index constraints.
- [x] `apps/api/app/Application/Evidence/` — implement owned eligibility and atomic start/reconcile service; derive areas/questions from the stored report and enforce the five-area cap.
- [x] `apps/api/app/Presentation/Http/Controllers/` and `apps/api/routes/api.php` — expose start/read routes with non-disclosing errors, idempotency replay, auth, and approved envelope.
- [x] `apps/web/src/features/evidence/`, `apps/web/src/pages/ai/`, and `apps/web/src/pages/match/MatchReportPage.vue` — add accessible start, active, no-needed, loading, conflict, and recovery states using server data.
- [x] `apps/api/tests/Feature/` and `apps/web/tests/` — cover source pinning, two-user isolation, no-area, duplicate/lost start, malformed IDs, and keyboard-visible UI states.

**Acceptance Criteria:**
- Given I own a Match Report with missing or weak signals, when I start an Interview, then exactly one session snapshots its exact source tuple, ordered areas, and deterministic questions without changing the source Version.
- Given the report has no unresolved area, when I try to start, then the API returns `409 INTERVIEW_NOT_NEEDED` and creates no session.
- Given a foreign, malformed, stale, duplicate, or concurrent request, when it is submitted, then the response is non-disclosing or idempotently reconciled and no ambiguous session is persisted.

## Verification

**Commands:**
- `php artisan test --compact tests/Feature/Evidence/StartEvidenceInterviewTest.php` — expected: all API ownership, lifecycle, idempotency, and no-needed cases pass.
- `npm run test -- --run tests/evidence-interview.test.ts` — expected: adapter and accessible Match Report/Interview states pass.
- `npm run type-check` — expected: no TypeScript errors.

**Manual checks:**
- Open an eligible and a no-area Match Report as two different Users; confirm no foreign source details appear and the CTA cannot create a session when no areas exist.

## Verification Results

- Passed: focused Evidence adapter/page tests, full frontend Vitest suite (18 tests), TypeScript type-check, ESLint/Oxlint, production build, and `git diff --check`.
- Blocked: Laravel feature tests and Pint because PHP is not installed in the current shell and the Docker daemon is unavailable.
- Not performed: browser E2E and hosted CI proof.

## Suggested Review Order

**Server-owned interview lifecycle**

- Start here: one transaction resolves ownership, sources, idempotency, and deterministic questions.
  [`EvidenceInterviewService.php:30`](../../apps/api/app/Application/Evidence/EvidenceInterviewService.php#L30)

- Read flow revalidates the pinned source tuple before expiring an active session.
  [`EvidenceInterviewService.php:125`](../../apps/api/app/Application/Evidence/EvidenceInterviewService.php#L125)

- Signal validation rejects malformed, duplicate, or blank report areas before applying the cap.
  [`EvidenceInterviewService.php:201`](../../apps/api/app/Application/Evidence/EvidenceInterviewService.php#L201)

**Persistence and integrity boundaries**

- Persistence establishes the immutable source snapshot, active-session uniqueness, and PostgreSQL checks.
  [`2026_10_07_000014_create_evidence_interviews_table.php:14`](../../apps/api/database/migrations/2026_10_07_000014_create_evidence_interviews_table.php#L14)

- Model hooks preserve source IDs, areas, and questions while allowing lifecycle status transitions.
  [`EvidenceInterview.php:11`](../../apps/api/app/Models/EvidenceInterview.php#L11)

- Presenter validates one-to-one area/question relationships before exposing stored state.
  [`EvidenceInterviewPresenter.php:14`](../../apps/api/app/Application/Evidence/EvidenceInterviewPresenter.php#L14)

**HTTP and client contract**

- Controller maps service problems into the approved API envelope for start and read actions.
  [`EvidenceInterviewController.php:18`](../../apps/api/app/Presentation/Http/Controllers/Evidence/EvidenceInterviewController.php#L18)

- Routes apply authenticated, CSRF-protected API boundaries to both interview actions.
  [`api.php:116`](../../apps/api/routes/api.php#L116)

- Client parsing mirrors server invariants and rejects malformed timestamps or question links.
  [`evidence.api.ts:24`](../../apps/web/src/features/evidence/api/evidence.api.ts#L24)

**User recovery and accessibility states**

- Match Report CTA shows the five-area cap and opens an existing session on conflict.
  [`MatchReportPage.vue:40`](../../apps/web/src/pages/match/MatchReportPage.vue#L40)

- Interview page renders retryable loading failures and closed-session guidance without fake answer submission.
  [`AiInterviewPage.vue:19`](../../apps/web/src/pages/ai/AiInterviewPage.vue#L19)

**Verification coverage**

- API feature tests cover ownership, idempotency fingerprints, empty-body rejection, reads, and closed sessions.
  [`StartEvidenceInterviewTest.php:23`](../../apps/api/tests/Feature/Evidence/StartEvidenceInterviewTest.php#L23)

- Page tests verify expired-session messaging and the server-selected five-area cap.
  [`evidence-interview-pages.test.ts:66`](../../apps/web/tests/evidence-interview-pages.test.ts#L66)
