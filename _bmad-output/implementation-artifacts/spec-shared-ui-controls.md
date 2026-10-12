---
title: 'Normalize web shared UI controls'
type: 'refactor'
created: '2026-10-12'
status: 'done'
baseline_commit: '2208be46c98144798002dc725783bc82c414b761'
review_loop_iteration: 0
context:
  - '/home/tuna2/notthing/cv/docs/frontend-architecture.md'
  - '/home/tuna2/notthing/cv/apps/web/components.json'
---

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

**Problem:** Web components use a mix of shared `ui` primitives and repeated raw form/action controls. This duplicates styling and risks inconsistent accessibility and future drift.

**Approach:** Make `ui` primitives the stable control layer, compose them through existing atoms/molecules/organisms where an abstraction exists, and migrate all current consumers without changing layout, public behavior, validation flow, or server contracts.

## Boundaries & Constraints

**Always:** Preserve existing props/events, model updates, debounce timing, validation bindings, ARIA IDs, labels, responsive classes, and accessible names. Add tests for primitive contracts and an architecture test that rejects raw `input`, `textarea`, `select`, `label`, and `button` outside `shared/components/ui`.

**Ask First:** Stop if a migration requires changing a product-facing interaction, server payload, or layout breakpoint not described here.

**Never:** Do not introduce visual-snapshot infrastructure, rewrite unrelated feature markup, create pass-through wrappers, or change semantic layout elements and RouterLink usage merely for reuse.

## I/O & Edge-Case Matrix

| Scenario | Input / State | Expected Output / Behavior | Error Handling |
|----------|--------------|----------------------------|----------------|
| Controlled input | Parent changes `modelValue` or user types | Primitive reflects value and emits the existing update event | No stale local value |
| Form field error | `FormField` has `error` and `htmlFor` | Label/control/error IDs remain associated; error wins over hint | Preserve `aria-invalid` and `aria-describedby` |
| Search debounce | User types, clears, or parent resets before 350ms | One current search/update emission; pending timer is cancelled on reset/unmount | No stale search event |
| Select selection | Existing CV version options and selected value | Select displays current value and emits the selected option | Disabled options remain disabled |

</frozen-after-approval>

## Code Map

- `apps/web/src/shared/components/ui/input/Input.vue` -- controlled input primitive; currently lacks a real `v-model` contract.
- `apps/web/src/shared/components/ui/select/Select.vue` -- Reka Select primitive; extend trigger attribute/ARIA forwarding.
- `apps/web/src/shared/components/ui/button/Button.vue` -- shared action primitive; add a text/link-compatible variant without changing existing variants.
- `apps/web/src/shared/components/ui/label/Label.vue` -- label primitive; preserve inherited `for` and required attributes.
- `apps/web/src/shared/components/ui/textarea/Textarea.vue` -- new multiline control primitive matching Input semantics.
- `apps/web/src/shared/components/molecules/FormField.vue` -- preserve scoped `controlProps`, error/hint precedence, and accessibility IDs while composing Label.
- `apps/web/src/shared/components/molecules/SearchInput.vue` -- compose Input/Button while preserving controlled debounce behavior.
- `apps/web/src/shared/components/PaginationNav.vue` -- migrate text appearance actions to Button.
- `apps/web/src/shared/components/organisms/AppHeader.vue` and `AppSidebar.vue` -- migrate menu/profile/collapse actions while preserving state and ARIA.
- `apps/web/src/features/auth/components/LoginForm.vue` and `RegisterForm.vue` -- migrate icon-wrapped inputs and demo action.
- `apps/web/src/features/cv/components/CvEditor.vue` and `CvSectionNav.vue` -- migrate fields, section editor, and section actions.
- `apps/web/src/features/jd/components/JdForm.vue` and `JdMatchCreator.vue` -- migrate fields, native select, retry actions, and preserve controller events.
- `apps/web/src/features/evidence/components/PatchReview.vue`, `apps/web/src/pages/ai/AiInterviewPage.vue`, `apps/web/src/features/dashboard/components/DashboardStats.vue`, and `apps/web/src/features/match/components/SkillGapList.vue` -- migrate remaining raw controls.
- `apps/web/tests/form-field.test.ts`, `web-review-regressions.test.ts`, `pagination-components.test.ts`, `cv-editor-regressions.test.ts`, `jd-route-regressions.test.ts`, and new primitive tests -- behavior/accessibility coverage.
- `apps/web/tests/web-architecture-boundaries.test.ts` -- enforce the no-raw-control boundary.
- `docs/frontend-architecture.md` -- document the one-way UI dependency rule.

## Tasks & Acceptance

**Execution:**
- [x] Implement Input, Textarea, Label, Select, and Button contracts; add focused primitive tests.
- [x] Refactor FormField and SearchInput; preserve existing regression tests and add coverage for the composed controls.
- [x] Migrate all listed shared, organism, page, and feature consumers; use AppIcon for decorative Lucide rendering where applicable.
- [x] Add the static raw-control architecture rule and update frontend architecture documentation.
- [x] Run type-check, unit tests, build, and relevant Playwright/manual checks.

**Acceptance Criteria:**
- Given the web source tree, when the architecture test scans it, then raw controls exist only inside `shared/components/ui`.
- Given any existing form or action flow, when a user interacts with it, then its existing model updates, validation, loading/disabled behavior, emitted events, and accessible labels remain intact.
- Given `FormField` with a hint or error, when it renders, then the same control/error/hint ID and ARIA relationships are present.
- Given SearchInput input, reset, or unmount, when debounce processing occurs, then no stale search emission is produced.
- Given the existing CV, JD, auth, dashboard, patch, AI, navigation, and pagination flows, when tests and manual responsive checks run, then they pass without layout or interaction regressions.

## Design Notes

Native controls stay inside the primitives so consumers get consistent contracts without losing native attributes. Reka Select remains the only non-native control migration and must expose consumer-facing identity/ARIA attributes on its trigger. AppIcon is for decorative icons; interactive or third-party component requirements may retain direct Lucide components.

## Verification

**Commands:**
- `cd apps/web && npm run type-check` -- succeeds.
- `cd apps/web && npm run test:unit` -- all unit tests pass.
- `cd apps/web && npm run build` -- production build succeeds.
- `cd apps/web && npm run test:e2e -- --project=chromium` -- relevant browser flows pass when browser dependencies are available.

**Manual checks:**
- Verify desktop/mobile auth, CV editor/version, JD save/analyze/match/select, search/clear, pagination/retry, header/sidebar menus, patch review, and AI answer fields retain layout, focus, labels, and disabled/loading behavior.

## Verification Results

- `npm run type-check --prefix apps/web` passed.
- `npm run lint --prefix apps/web` passed.
- `npm run test:unit --prefix apps/web -- --run` passed: 22 files, 86 tests.
- `npm run build --prefix apps/web` passed.
- `git diff --check` passed.
- `apps/web/tests/shared-ui-controls.test.ts` covers controlled Input/Textarea updates, Label attributes, Select trigger ARIA forwarding, and Button link/loading behavior.
- The browser E2E command and manual responsive checks were not run in this environment.

## Suggested Review Order

**Primitive contracts**

- Controlled value, native attributes, disabled state, and validation styling form the reusable input contract.
  [`Input.vue:4`](../../apps/web/src/shared/components/ui/input/Input.vue#L4)

- Multiline fields mirror Input without introducing a second consumer-facing binding model.
  [`Textarea.vue:4`](../../apps/web/src/shared/components/ui/textarea/Textarea.vue#L4)

- Select forwards identity and ARIA attributes to the interactive trigger while preserving option behavior.
  [`Select.vue:24`](../../apps/web/src/shared/components/ui/select/Select.vue#L24)

- Action variants preserve loading semantics while supporting text-like controls and custom consumer styling.
  [`Button.vue:4`](../../apps/web/src/shared/components/ui/button/Button.vue#L4)

**Composition and migration**

- FormField now composes the shared Label while retaining scoped control and error/hint precedence contracts.
  [`FormField.vue:20`](../../apps/web/src/shared/components/molecules/FormField.vue#L20)

- SearchInput composes Input, Button, and AppIcon without changing its controlled 350ms debounce lifecycle.
  [`SearchInput.vue:25`](../../apps/web/src/shared/components/molecules/SearchInput.vue#L25)

- JD version selection migrates from native select while preserving reactive controller state and server payloads.
  [`JdMatchCreator.vue:9`](../../apps/web/src/features/jd/components/JdMatchCreator.vue#L9)

**Guardrails and verification**

- The architecture test rejects raw controls outside the shared UI primitive boundary.
  [`web-architecture-boundaries.test.ts:31`](../../apps/web/tests/web-architecture-boundaries.test.ts#L31)

- Primitive tests lock controlled updates, forwarded attributes, ARIA identity, disabled options, and loading behavior.
  [`shared-ui-controls.test.ts:11`](../../apps/web/tests/shared-ui-controls.test.ts#L11)

- The migrated Playwright flow selects the Reka trigger and verifies the actual match request payload.
  [`epic2-job-fit.spec.ts:45`](../../apps/web/tests/e2e/epic2-job-fit.spec.ts#L45)

- Architecture documentation records the one-way primitive dependency rule for future consumers.
  [`frontend-architecture.md:21`](../../docs/frontend-architecture.md#L21)
