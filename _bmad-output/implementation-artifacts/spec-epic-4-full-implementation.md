---
title: 'Epic 4 full implementation: Evidence-based AI revision'
type: 'feature'
created: '2026-10-08'
status: 'done'
baseline_commit: '2a799bc8dca454d2fcb5a7df2e479c2243619102'
review_loop_iteration: 0
context:
  - D:/CareerFitCV/_bmad-output/implementation-artifacts/epic-4-context.md
  - D:/CareerFitCV/_bmad-output/planning-artifacts/epics/epic-4-ai-revision/contracts.md
  - D:/CareerFitCV/_bmad-output/planning-artifacts/epics/epic-4-ai-revision/data-and-lifecycle.md
  - D:/CareerFitCV/_bmad-output/planning-artifacts/epics/epic-4-ai-revision/security-and-access.md
  - D:/CareerFitCV/_bmad-output/planning-artifacts/epics/epic-4-ai-revision/test-strategy.md
  - D:/CareerFitCV/apps/api/AGENTS.md
---

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

**Problem:** Story 4.1 now starts a server-owned Evidence Interview, but the
remaining Epic 4 lifecycle cannot yet collect immutable User Evidence, produce a
bounded Patch proposal, let the User decide, apply one approved change to a new
CV Version, or regenerate a rejected proposal.

**Approach:** Complete Stories 4.2–4.6 as one dependency-ordered vertical slice:
answer/provenance first, deterministic fake-provider generation second, explicit
review decisions third, atomic immutable-Version apply fourth, and predecessor-
linked regeneration last. Preserve the Story 4.1 source tuple and API envelope.

## Boundaries & Constraints

**Always:** Authorize every action through User → Match Report → pinned CV/JD
revision/Analysis → Interview/Evidence/Patch. Answers are exactly `answer` or
`cannot_provide`, original text is immutable, normalized text is derived, and
only positive User Evidence can support a Patch. Patches allow only summary
replacement, one existing Experience/Project highlight replacement, or one
highlight append. Generation is synchronous, deterministic fake-provider-only,
15-second bounded, rate/concurrency limited, idempotent, and never stores raw
prompt/output. Patch mutations require `If-Match`; approval locks, revalidates,
validates the complete snapshot, creates exactly one immutable CV Version, and
records provenance before marking the Patch applied.

**Ask First:** Stop only if implementation would change the approved `/api/v1`
envelopes, source/version contracts, allowlist, human-confirmation boundary, or
the Epic 2 Match Report contract; production-provider activation, queue/worker,
retention, and launch approval remain separate gates.

**Never:** Do not accept client owner/source/status/snapshot/provider authority,
reopen or edit an answer, reopen a predecessor Patch, auto-apply proposals,
persist raw provider content, call a production AI provider, or weaken ownership,
stale-source, concurrency, or non-disclosure rules.

## I/O & Edge-Case Matrix

| Scenario | Input / State | Expected Output / Behavior | Error Handling |
|----------|---------------|-----------------------------|----------------|
| ANSWER | Active owned Interview, exact current question | Immutable User Evidence and progress; close only after all outcomes | `422 VALIDATION_FAILED` for malformed/excess text |
| ANSWER_CONFLICT | Duplicate, stale, expired, foreign, or out-of-order answer | No ambiguous transition; idempotent replay when identical | `409 EVIDENCE_SESSION_CONFLICT` |
| GENERATE | Completed Interview with positive Evidence | One validated bounded Patch and sanitized attempt record | `503 PATCH_PROVIDER_UNAVAILABLE`, `422 PATCH_PROPOSAL_INVALID`, or rate conflict; no Patch on failure |
| DECIDE | Pending Patch with matching `If-Match` | Read, bounded edit, or explicit reject with immutable provenance | `409 PATCH_STATE_CONFLICT` for stale/competing action |
| APPROVE | Pending Patch, fresh confirmation, exact old value | One new validated CV Version and applied Patch atomically | `409 PATCH_SOURCE_STALE`; transaction failure rolls back all writes |
| REGENERATE | Rejected or safely later-invalid predecessor | New Patch linked by `predecessor_patch_id`; predecessor unchanged | Context/stale/foreign failure before provider call |

</frozen-after-approval>

## Code Map

- `apps/api/app/Application/Evidence/EvidenceInterviewService.php` — existing source ownership, session locking, expiry, and question snapshot boundary.
- `apps/api/app/Models/EvidenceInterview.php` and `apps/api/database/migrations/2026_10_07_000014_create_evidence_interviews_table.php` — immutable interview source and lifecycle persistence.
- `apps/api/app/Application/Cv/CvIdempotency.php`, `ApiProblem.php`, `CanonicalJson.php`, and `ProfileDocumentValidator.php` — replay, error, canonical hashing, and complete-snapshot validation conventions.
- `apps/api/app/Application/Cv/VersionService.php`, `CvVersion.php`, and `apps/api/database/migrations/` — immutable result-Version creation and provenance extension point; do not mutate existing Version semantics.
- `apps/api/routes/api.php` and `apps/api/app/Presentation/Http/Controllers/` — protected `/api/v1` route and envelope conventions to extend for answers, patches, decisions, approval, and regeneration.
- `apps/web/src/features/evidence/`, `apps/web/src/pages/ai/AiInterviewPage.vue`, `apps/web/src/pages/cv/PatchReviewPage.vue`, and `apps/web/src/app/router/index.ts` — current server-backed Interview flow and placeholder Patch review route to complete.
- `apps/api/tests/Feature/Evidence/`, `apps/api/tests/Feature/Patch/`, and `apps/web/tests/` — focused ownership, PostgreSQL transaction/race, provider-safety, lifecycle, accessibility, and regression coverage.

## Tasks & Acceptance

**Execution:**
- [x] `apps/api/app/Models/`, `database/migrations/`, `Application/Evidence/`, and Evidence controllers — persist immutable answers, enforce exact outcomes/provenance/progress, and expose answer/read contracts.
- [x] `apps/api/app/Application/Patch/`, provider DTO/fake, migrations/models, and controllers — implement strict allowlisted generation, sanitized attempts, rate/concurrency limits, idempotency, and regeneration reuse.
- [x] Patch decision controllers/services/models — implement read, bounded edit, reject, `If-Match`, explicit confirmation, state conflicts, and immutable decision provenance.
- [x] Version apply service/migration/provenance and Patch approval route — atomically revalidate, transform, validate, create exactly one immutable CV Version, and roll back on failure.
- [x] `apps/web/src/features/evidence/`, Patch feature/page, router, and tests — replace placeholders with answer, generation, decision, approval, regeneration, loading, stale, retry, and accessible recovery states.
- [x] API/frontend tests and documentation fixtures — cover the implemented lifecycle paths, malformed input, source immutability, and recovery contracts; PostgreSQL race/browser proof remains environment-gated.

**Acceptance Criteria:**
- Given an active Interview, when every question receives one valid outcome, then Evidence is immutable, provenance is `user`, and generation becomes available without changing the source Version.
- Given grounded positive Evidence, when generation succeeds, then exactly one validated allowlisted Patch is returned; malformed, unsupported, ungrounded, unavailable, duplicate, or rate-limited attempts create no trusted Patch.
- Given a pending Patch, when the User edits, rejects, approves, or regenerates with fresh preconditions, then the allowed state transition and audit lineage are persisted; stale competitors cannot win.
- Given approval, when any source, target, Evidence, or snapshot validation fails, then no new CV Version or applied state remains; successful approval creates one immutable Version with exact provenance.

## Design Notes

The implementation is a single stateful spine, not provider-driven persistence:
`Interview → Evidence → Patch proposal → User decision → immutable Version`.
The fake provider receives a minimal DTO and returns a typed proposal DTO; all
trusted state is created by Laravel after validation. Stories remain dependency-
ordered even though this umbrella scope is approved as one delivery.

## Verification

**Commands:**
- `php artisan test --compact tests/Feature/Evidence/AnswerEvidenceQuestionsTest.php tests/Feature/Patch/GeneratePatchProposalTest.php tests/Feature/Patch/PatchDecisionTest.php tests/Feature/Patch/PatchApplyTest.php tests/Feature/Patch/PatchRegenerationTest.php` — expected: all backend lifecycle, ownership, race, and rollback cases pass on PostgreSQL.
- `composer test` and `vendor/bin/pint --dirty --format agent` — expected: full PHPUnit suite and clean PHP formatting.
- `npm run test:unit -- --run`, `npm run type-check`, `npm run lint`, and `npm run build` — expected: frontend tests and production checks pass.
- `npm run test:e2e -- tests/e2e/epic4-ai-revision.spec.ts` — expected: answer, proposal, decision, approval, regeneration, stale, and recovery journeys pass when browser/runtime services are available.
- `git diff --check` — expected: no whitespace errors.

**Manual checks:**
- Verify User Evidence, provider proposal, User edit, rejection, predecessor, and result Version are visually distinct; no client can submit owner/source/status/provider authority or a composed CV snapshot.

## Suggested Review Order

**Lifecycle orchestration**

- Start with the server-owned orchestration, locking, validation, and transaction boundaries.
  [`PatchService.php:26`](../../apps/api/app/Application/Patch/PatchService.php#L26)

- Trace immutable answer creation, exact question checks, progress, and completion transitions.
  [`EvidenceAnswerService.php:17`](../../apps/api/app/Application/Evidence/EvidenceAnswerService.php#L17)

- Review the full ownership graph and pinned source consistency checks.
  [`PatchService.php:384`](../../apps/api/app/Application/Patch/PatchService.php#L384)

**Persistence and API contracts**

- Inspect answer immutability, provenance, uniqueness, and PostgreSQL constraints.
  [`2026_10_08_000015_create_evidence_answers_table.php:14`](../../apps/api/database/migrations/2026_10_08_000015_create_evidence_answers_table.php#L14)

- Inspect Patch, provider-attempt, regeneration uniqueness, and approval provenance schema.
  [`2026_10_08_000016_create_patch_lifecycle_tables.php:14`](../../apps/api/database/migrations/2026_10_08_000016_create_patch_lifecycle_tables.php#L14)

- Review the protected API surface and middleware coverage for every lifecycle mutation.
  [`api.php:123`](../../apps/api/routes/api.php#L123)

- Verify server-derived actions and cited Evidence are the only review projection exposed.
  [`PatchPresenter.php:11`](../../apps/api/app/Application/Patch/PatchPresenter.php#L11)

**Immutable Version apply**

- Follow approval revalidation, complete-snapshot transformation, provenance, and atomic state change.
  [`PatchService.php:269`](../../apps/api/app/Application/Patch/PatchService.php#L269)

- Check CV Version provenance fields remain additive to the existing immutable Version contract.
  [`2026_10_08_000017_add_patch_provenance_to_cv_versions.php:13`](../../apps/api/database/migrations/2026_10_08_000017_add_patch_provenance_to_cv_versions.php#L13)

**Frontend journey**

- Review answer drafts, idempotent retries, progress, closed-session messaging, and recovery.
  [`AiInterviewPage.vue:18`](../../apps/web/src/pages/ai/AiInterviewPage.vue#L18)

- Review Patch diff rendering, server-derived actions, confirmation, stale recovery, and lineage navigation.
  [`PatchReviewPage.vue:14`](../../apps/web/src/pages/cv/PatchReviewPage.vue#L14)

- Inspect transport schemas that reject malformed lifecycle responses before rendering.
  [`evidence.api.ts:1`](../../apps/web/src/features/evidence/api/evidence.api.ts#L1)

**Verification**

- Read lifecycle feature coverage for answer, generation, decision, approval, and regeneration contracts.
  [`PatchApplyTest.php:1`](../../apps/api/tests/Feature/Patch/PatchApplyTest.php#L1)

- Confirm component coverage exercises server-derived Patch controls and source/proposal separation.
  [`evidence-interview-pages.test.ts:1`](../../apps/web/tests/evidence-interview-pages.test.ts#L1)
