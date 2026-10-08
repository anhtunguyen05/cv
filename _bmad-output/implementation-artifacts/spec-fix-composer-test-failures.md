---
title: 'Fix Epic 4 composer test failures'
type: 'bugfix'
created: '2026-10-08'
baseline_commit: '87d2e0ab8007c4bc91a517eba372e028dcd1df08'
status: 'done'
review_loop_iteration: 0
context:
  - '{project-root}/apps/api/AGENTS.md'
  - '{project-root}/_bmad-output/planning-artifacts/epics/epic-4-ai-revision/contracts.md'
---

<frozen-after-approval reason="human-owned intent - do not modify unless human renegotiates">

## Intent

**Problem:** `composer test` reports five failures in Epic 4 Evidence and Patch flows. The failures cover loss of original Evidence answer text, JSONB key-order-sensitive snapshot assertions, stale authenticated users between test requests, and approval rejection of a fixture snapshot that does not satisfy the immutable CV Version contract.

**Approach:** Preserve raw Evidence answer text before Laravel's global trimming middleware, make snapshot assertions compare canonical JSON semantics, reset the test auth guard when switching owners, and make the shared Epic 4 fixture create a production-shaped Version snapshot with its required title.

## Boundaries & Constraints

**Always:** Keep `answer_original` byte/content-preserving after JSON decoding while retaining the normalized value for validation and provider grounding. Preserve non-disclosing `404 RESOURCE_NOT_FOUND` behavior for foreign Interview and Patch resources. Keep CV snapshots immutable and validate the complete production snapshot shape before applying a Patch.

**Ask First:** Halt if resolving a failure requires changing the public Evidence/Patch contract, weakening ownership checks, or changing unrelated Epic 1/2 behavior.

**Never:** Do not weaken `PatchService` ownership queries, remove snapshot validation, broaden test expectations to accept a malformed Version snapshot, or change unrelated frontend, migration, or provider behavior.

## I/O & Edge-Case Matrix

| Scenario | Input / State | Expected Output / Behavior | Error Handling |
|----------|--------------|---------------------------|----------------|
| ORIGINAL_ANSWER | Answer contains outer spaces and CRLF | API response and persisted original retain decoded input; normalized storage uses trimmed LF/NFC text | Invalid normalized content remains `422 VALIDATION_FAILED` |
| FOREIGN_RESOURCE | Auth guard switches from owner to another User | Interview and Patch reads/decisions return non-disclosing `404 RESOURCE_NOT_FOUND` | No foreign state changes |
| APPROVE_PATCH | Complete Interview and valid Patch use a production-shaped source Version | Approval returns `200`, creates exactly one immutable result Version, and marks Patch applied | Invalid source snapshot remains rejected |
| JSONB_RELOAD | Snapshot is read back with reordered object keys | Equality assertion compares canonical semantic content | No production data mutation |

## Code Map

- `apps/api/bootstrap/app.php:20-27` -- global request middleware; preserve the `answer` field needed by the raw Evidence contract.
- `apps/api/app/Application/Evidence/EvidenceAnswerService.php:86-132` -- separates original and normalized answer values; retain this boundary.
- `apps/api/app/Application/Patch/PatchService.php:269-340,663-671` -- approval revalidation and complete snapshot validation; do not weaken production validation.
- `apps/api/tests/Feature/Evidence/AnswerEvidenceQuestionsTest.php:19-66` -- verifies raw answer and immutable completion behavior.
- `apps/api/tests/Feature/Evidence/StartEvidenceInterviewTest.php:159-174` -- switches ownership for foreign Interview reads and compares source snapshots.
- `apps/api/tests/Feature/Evidence/CreatesEpic4Context.php:18-105` -- shared Epic 4 fixture for source-pinned Versions and Patch approval.
- `apps/api/tests/Feature/Patch/PatchDecisionTest.php:46-63` -- switches ownership for foreign Patch decisions.
- `docs/contracts/cv/profile-v1.md:1-70` -- approved snapshot/profile shape and canonical text rules.
- `_bmad-output/planning-artifacts/epics/epic-4-ai-revision/contracts.md:50-92` -- approved raw/normalized Evidence and atomic Patch approval contracts.

## Tasks & Acceptance

**Execution:**

- [x] `apps/api/bootstrap/app.php` -- exempt the Evidence `answer` input from generic trimming while leaving service validation and normalized search storage unchanged.
- [x] `apps/api/tests/Feature/Evidence/StartEvidenceInterviewTest.php` and `apps/api/tests/Feature/Patch/PatchDecisionTest.php` -- reset Laravel auth guards before switching Users so ownership assertions exercise the intended identity.
- [x] `apps/api/tests/Feature/Evidence/StartEvidenceInterviewTest.php` -- compare reloaded JSON snapshots canonically instead of depending on PostgreSQL JSONB key order.
- [x] `apps/api/tests/Feature/Evidence/CreatesEpic4Context.php` -- include the profile title in direct-created Version snapshots and hash the same canonical snapshot used by approval validation.

**Acceptance Criteria:**

- Given an answer containing outer whitespace and CRLF, when it is submitted, then the API preserves the original decoded answer and stores a separately normalized search value.
- Given an Interview or Patch owned by User A, when User B reads or decides it after the test guard is reset, then the API returns `404 RESOURCE_NOT_FOUND`.
- Given a valid completed Interview Patch with a production-shaped source snapshot, when User A approves it, then exactly one immutable result Version is created and the Patch becomes `applied`.
- Given a PostgreSQL JSONB round trip, when the source snapshot is compared, then the test compares canonical content rather than object-key insertion order.

## Verification

**Commands:**

- `vendor/bin/phpunit --configuration phpunit.xml --filter 'AnswerEvidenceQuestionsTest|StartEvidenceInterviewTest|PatchApplyTest|PatchDecisionTest'` -- expected: focused Evidence/Patch tests pass.
- `composer test` -- expected: all configured PHPUnit tests pass.
- `vendor/bin/pint --test` -- expected: PHP style check passes.
- `git diff --check` -- expected: no whitespace errors.

</frozen-after-approval>

## Suggested Review Order

1. **Request boundary** -- confirm the raw-answer exception is scoped to the Evidence answer route: [apps/api/bootstrap/app.php:27](../../apps/api/bootstrap/app.php:27).
2. **Snapshot/apply fixtures** -- verify the shared fixture matches the production Version snapshot contract: [apps/api/tests/Feature/Evidence/CreatesEpic4Context.php:36](../../apps/api/tests/Feature/Evidence/CreatesEpic4Context.php:36).
3. **PostgreSQL/test isolation** -- inspect canonical JSON comparison and explicit guard resets: [apps/api/tests/Feature/Evidence/StartEvidenceInterviewTest.php:53](../../apps/api/tests/Feature/Evidence/StartEvidenceInterviewTest.php:53), [apps/api/tests/Feature/Patch/PatchDecisionTest.php:53](../../apps/api/tests/Feature/Patch/PatchDecisionTest.php:53).
