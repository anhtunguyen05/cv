---
title: 'Fix frontend Prettier formatting failures across the source tree'
type: 'chore'
created: '2026-10-08'
status: 'done'
route: 'one-shot'
---

# Fix frontend Prettier formatting failures across the source tree

## Intent

**Problem:** `npx prettier --check src/` reported formatting violations in 27 files under the web application's source tree, causing the frontend formatting check to fail.

**Approach:** Apply the repository's existing Prettier configuration to `apps/web/src/` and verify the complete source tree passes the formatting check without changing runtime behavior.

## Suggested Review Order

**API and data-flow formatting**

- Review the authentication API formatting changes first to confirm request and response structure is unchanged.
  [`auth.api.ts:1`](../../apps/web/src/features/auth/api/auth.api.ts#L1)

- Review CV query formatting around query keys and option objects for behavior-preserving wrapping.
  [`cv.queries.ts:1`](../../apps/web/src/features/cv/api/cv.queries.ts#L1)

**Feature components and composables**

- Review the CV editor component and draft operations for formatting-only template and callback changes.
  [`CvEditor.vue:1`](../../apps/web/src/features/cv/components/CvEditor.vue#L1)

- Review JD workflow components and controllers for unchanged mutation and error-handling logic.
  [`JdEditor.vue:1`](../../apps/web/src/features/jd/components/JdEditor.vue#L1)

- Review evidence and match presentation changes for unchanged approval and report-rendering behavior.
  [`PatchReview.vue:1`](../../apps/web/src/features/evidence/components/PatchReview.vue#L1)

**Application and shared UI**

- Review the landing page and marketing sections for formatting-only markup changes.
  [`LandingPage.vue:1`](../../apps/web/src/pages/marketing/LandingPage.vue#L1)

- Review shared form field formatting as the final reusable-component check.
  [`FormField.vue:1`](../../apps/web/src/shared/components/molecules/FormField.vue#L1)
