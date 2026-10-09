---
title: 'Relocate auth mutation orchestration to composables'
type: 'refactor'
created: '2026-10-09'
status: 'done'
baseline_commit: '98cfefd3ee06a4ef86cfefe39b5431e8dc0ab20d'
review_loop_iteration: 0
context:
  - 'docs/frontend-architecture.md'
---

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

**Problem:** Authentication mutation hooks are located in `features/auth/api/auth.mutations.ts`, although they coordinate Pinia state, routing, and Vue Query cache cleanup in addition to making HTTP requests. This makes the API directory contain UI/application workflow code, unlike the other implemented features.

**Approach:** Move the existing auth mutation hooks to an auth composable module and update all internal imports and the public feature export. Preserve every current API contract and runtime behavior.

## Boundaries & Constraints

**Always:** Keep `auth.api.ts` as the HTTP, CSRF recovery, and runtime-validation boundary. Preserve exported mutation names, login return-to validation, registration lost-response reconciliation, logout cache cleanup, redirects, and the current Pinia store behavior. Keep the API, router guard, global auth-expired handler, component behavior, and public feature exports compatible.

**Ask First:** Do not expand this naming/boundary refactor into a broader feature-folder reorganization, documentation rewrite, or behavior/test redesign without approval.

**Never:** Do not change endpoint URLs, request payloads, CSRF behavior, validation, authentication semantics, store state, redirect destinations, or cache-clearing order. Do not edit backend code or planned-product artifacts.

## I/O & Edge-Case Matrix

| Scenario | Input / State | Expected Output / Behavior | Error Handling |
|----------|---------------|----------------------------|----------------|
| Login | Valid credentials and a safe `return_to` | Same user is stored and navigation goes to the requested in-app path | Existing mutation error is exposed unchanged |
| Login redirect | `return_to` is external-like (`//...`) or contains a backslash | Navigation falls back to the dashboard | Unsafe value is ignored |
| Registration uncertainty | Transport-style failure after request dispatch | Existing one-time `getMe` reconciliation remains available | Known HTTP/validation errors remain retryable without reconciliation |
| Logout | Successful logout | Auth state is cleared, query work/cache is cleared, then navigation goes to login | Existing mutation error is exposed unchanged |

</frozen-after-approval>

## Code Map

- `apps/web/src/features/auth/api/auth.api.ts` -- HTTP-only auth functions, CSRF bootstrap/recovery, and Zod parsing; read-only in this refactor.
- `apps/web/src/features/auth/api/auth.mutations.ts` -- current mutation hooks and private safe-return helper; relocate without changing executable behavior.
- `apps/web/src/features/auth/composables/useAuth.ts` -- facade that imports and exposes all three mutation hooks; update its relative import.
- `apps/web/src/features/auth/components/LoginForm.vue` -- direct login-mutation consumer; update import only.
- `apps/web/src/features/auth/components/RegisterForm.vue` -- direct registration-mutation consumer; update import only.
- `apps/web/src/features/auth/index.ts` -- feature public exports; retain the same exported symbols from their new path.
- `apps/web/src/app/router/index.ts` and `apps/web/src/main.ts` -- separate router/bootstrap ownership of `getMe` and auth-expired cleanup; do not change.
- `apps/web/tests/auth-registration.test.ts` -- covers auth API CSRF/parse behavior, which must continue to pass after the move.

## Tasks & Acceptance

**Execution:**

- [x] `apps/web/src/features/auth/composables/useAuthMutations.ts` -- relocate the three Vue Query mutation hooks and `safeReturnTo`; adjust only the relative API import.
- [x] `apps/web/src/features/auth/api/auth.mutations.ts` -- remove the obsolete duplicate after relocation.
- [x] `apps/web/src/features/auth/composables/useAuth.ts`, `apps/web/src/features/auth/components/LoginForm.vue`, `apps/web/src/features/auth/components/RegisterForm.vue`, and `apps/web/src/features/auth/index.ts` -- repoint internal imports/re-export to the composable module while preserving their symbol names.
- [x] `apps/web` verification -- run focused auth API regression coverage plus TypeScript, lint, and diff checks; report any environment block separately.

**Acceptance Criteria:**

- Given an existing consumer imports `useLoginMutation`, `useRegisterMutation`, or `useLogoutMutation` from the feature barrel, when the refactor is complete, then the same symbols remain available with the same behavior.
- Given login, registration, or logout succeeds, when its mutation callback runs, then its current store, cache, and navigation side effects are unchanged.
- Given auth HTTP behavior is exercised, when focused regression checks run, then CSRF recovery and malformed-response handling remain intact.
- Given the auth feature is inspected, when code ownership is assessed, then its `api` directory contains HTTP-boundary code and mutation orchestration resides under `composables`.

## Spec Change Log

## Design Notes

The module boundary follows actual responsibility rather than the generic filename example in the older frontend architecture summary: a mutation hook that calls Pinia, Router, and QueryClient is a feature composable. The HTTP functions it invokes remain in `api/auth.api.ts`.

## Verification

**Commands:**

- `cd apps/web && npx vitest run tests/auth-registration.test.ts` -- expected: existing registration API tests pass.
- `npm run type-check --prefix apps/web` -- expected: no TypeScript errors.
- `npm run lint --prefix apps/web` -- expected: ESLint and Oxlint pass.
- `git diff --check` -- expected: no whitespace errors.

## Suggested Review Order

**Mutation ownership**

- Mutation hooks now live with auth workflow orchestration and preserve behavior.
  [`useAuthMutations.ts:22`](../../apps/web/src/features/auth/composables/useAuthMutations.ts#L22)

- Logout cleanup retains the required auth, query-cache, and navigation sequence.
  [`useAuthMutations.ts:65`](../../apps/web/src/features/auth/composables/useAuthMutations.ts#L65)

**Consumer boundary**

- The shared auth facade now consumes mutation hooks from the composables boundary.
  [`useAuth.ts:3`](../../apps/web/src/features/auth/composables/useAuth.ts#L3)

- Public feature exports retain the same hook names after relocation.
  [`index.ts:3`](../../apps/web/src/features/auth/index.ts#L3)

**Verification**

- Focused tests cover safe redirects, registration reconciliation, and logout ordering.
  [`auth-mutations.test.ts:58`](../../apps/web/tests/auth-mutations.test.ts#L58)

- Existing HTTP-boundary tests remain unchanged and cover CSRF and response validation.
  [`auth-registration.test.ts:31`](../../apps/web/tests/auth-registration.test.ts#L31)
