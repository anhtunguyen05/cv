---
title: 'Relocate PaginationNav into shared molecules'
type: 'refactor'
created: '2026-10-13'
status: 'done'
baseline_commit: 'f87359b4d225dfbbc4954991ec6f4731e485a3c4'
review_loop_iteration: 0
context:
  - '{project-root}/apps/web/vue-frontend-architecture.md'
---

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

**Problem:** `PaginationNav.vue` is a composite shared UI control but sits directly under `shared/components`, unlike the established atom/molecule/organism taxonomy. The placement makes the component's ownership less discoverable.

**Approach:** Relocate the component to the shared `molecules` directory, update every static import and the architecture document, and preserve its public props, emitted events, accessibility behavior, and rendered output.

## Boundaries & Constraints

**Always:** Work on the current branch; use the `@/shared/components/molecules/PaginationNav.vue` import path everywhere; retain the component implementation unchanged; update every in-repository consumer and the focused test import.

**Ask First:** Ask before changing public component behavior, creating a compatibility re-export at the former path, changing other component classifications, or touching feature/query logic.

**Never:** Do not alter pagination state, labels, event semantics, accessibility attributes, styles, or unrelated feature code. Do not add a duplicate component at the old path.

</frozen-after-approval>

## Code Map

- `apps/web/src/shared/components/PaginationNav.vue` -- current shared pager; move unchanged to `molecules/PaginationNav.vue`.
- `apps/web/src/features/dashboard/components/CvProfileList.vue` and `JdList.vue` -- dashboard consumers requiring the new alias import.
- `apps/web/src/features/cv/components/CvEditor.vue` and `CvVersionSelector.vue` -- CV consumers requiring the new alias import.
- `apps/web/src/features/jd/components/JdMatchCreator.vue` and `apps/web/src/features/match/components/MatchReportList.vue` -- additional feature consumers requiring the new alias import.
- `apps/web/tests/pagination-components.test.ts` -- direct focused-test import requiring the new alias path; behavior assertions remain unchanged.
- `apps/web/vue-frontend-architecture.md` -- tree diagram and molecule list currently disagree; make both show `PaginationNav` as a molecule.

## Tasks & Acceptance

**Execution:**
- [x] `apps/web/src/shared/components/molecules/PaginationNav.vue` -- relocate the existing pager source without behavioral edits, and remove the old root-level source -- align filesystem taxonomy without a duplicate implementation.
- [x] `apps/web/src/features/dashboard/components/{CvProfileList,JdList}.vue`, `apps/web/src/features/cv/components/{CvEditor,CvVersionSelector}.vue`, `apps/web/src/features/jd/components/JdMatchCreator.vue`, and `apps/web/src/features/match/components/MatchReportList.vue` -- replace the old import with the molecule path -- keep every production consumer resolving the same component.
- [x] `apps/web/tests/pagination-components.test.ts` -- update the direct import only -- retain the existing focused component coverage.
- [x] `apps/web/vue-frontend-architecture.md` -- place `PaginationNav.vue` under `molecules/` in its tree example and retain it in the molecule inventory -- synchronize documentation with the source taxonomy.

**Acceptance Criteria:**
- Given any current pagination consumer, when the application or focused test resolves `PaginationNav`, then it imports the single source at `shared/components/molecules/PaginationNav.vue`.
- Given the relocated component, when rendered at its first or final page, then its existing disabled navigation, labels, and polite live page indicator remain unchanged.
- Given the frontend architecture document, when a reader checks the component tree and molecule examples, then both classify `PaginationNav` as a molecule.
- Given the source tree, when searching for the old root-level PaginationNav import, then no references remain.

## Spec Change Log

## Design Notes

This is a source relocation, not an API migration. Updating static imports rather than retaining an old-path forwarding component intentionally lets the type checker detect any missed consumer.

## Verification

**Commands:**
- `npm run test:unit --prefix apps/web -- tests/pagination-components.test.ts` -- expected: focused pager tests pass.
- `npm run type-check --prefix apps/web` -- expected: all Vue/TypeScript imports resolve.
- `npm run lint --prefix apps/web` -- expected: lint succeeds.
- `npm run build --prefix apps/web` -- expected: production build succeeds.
- `git diff --check` -- expected: no whitespace errors.
- `rg -n 'shared/components/PaginationNav\\.vue' apps/web` -- expected: no matches.

## Suggested Review Order

**Shared component boundary**

- The pager now belongs with other reusable composite controls.
  [`PaginationNav.vue:1`](../../apps/web/src/shared/components/molecules/PaginationNav.vue#L1)

- The architecture tree now matches the source taxonomy.
  [`vue-frontend-architecture.md:173`](../../apps/web/vue-frontend-architecture.md#L173)

**Consumer resolution**

- CV editing resolves the relocated shared control through the molecule boundary.
  [`CvEditor.vue:8`](../../apps/web/src/features/cv/components/CvEditor.vue#L8)

- Dashboard pagination follows the same canonical import convention.
  [`CvProfileList.vue:8`](../../apps/web/src/features/dashboard/components/CvProfileList.vue#L8)

**Regression coverage**

- Focused tests import and exercise the pager from its new location.
  [`pagination-components.test.ts:3`](../../apps/web/tests/pagination-components.test.ts#L3)
