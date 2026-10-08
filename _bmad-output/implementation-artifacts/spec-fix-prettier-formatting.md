---
title: 'Fix frontend Prettier formatting failures'
type: 'chore'
created: '2026-10-08'
status: 'done'
route: 'one-shot'
---

# Fix frontend Prettier formatting failures

## Intent

**Problem:** `npx prettier --check src/` reported formatting violations in the evidence API module and three AI revision/match pages.

**Approach:** Apply the repository's existing Prettier configuration only to the four reported frontend files, preserving runtime behavior and confirming the complete `src/` tree passes the formatting check.

## Suggested Review Order

- Review schema and API wrapping changes as formatting-only restructuring.
  [`evidence.api.ts:24`](../../apps/web/src/features/evidence/api/evidence.api.ts#L24)

- Review interview interaction layout changes without altered mutation behavior.
  [`AiInterviewPage.vue:6`](../../apps/web/src/pages/ai/AiInterviewPage.vue#L6)

- Review patch decision handlers and template wrapping for unchanged actions.
  [`PatchReviewPage.vue:6`](../../apps/web/src/pages/cv/PatchReviewPage.vue#L6)

- Review Match Report interview-state expressions and presentation formatting.
  [`MatchReportPage.vue:48`](../../apps/web/src/pages/match/MatchReportPage.vue#L48)
