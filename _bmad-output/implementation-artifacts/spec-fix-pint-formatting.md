---
title: 'Fix Laravel Pint formatting failures'
type: 'chore'
created: '2026-10-08'
status: 'done'
route: 'one-shot'
---

# Fix Laravel Pint formatting failures

## Intent

**Problem:** `vendor/bin/pint --test` reported three style violations across the Patch service, evidence-interview migration, and evidence-answer feature test.

**Approach:** Apply Laravel Pint's existing formatting rules to only the three reported files, preserving application behavior and validating the result with Pint's test mode.

## Suggested Review Order

- Review the corrected transaction indentation and confirm behavior is unchanged.
  [`PatchService.php:167`](../../apps/api/app/Application/Patch/PatchService.php#L167)

- Review normalized migration quoting while preserving the SQL statements.
  [`2026_10_07_000014_create_evidence_interviews_table.php:53`](../../apps/api/database/migrations/2026_10_07_000014_create_evidence_interviews_table.php#L53)

- Review explicit imports replacing fully qualified references in the feature test.
  [`AnswerEvidenceQuestionsTest.php:7`](../../apps/api/tests/Feature/Evidence/AnswerEvidenceQuestionsTest.php#L7)
