---
title: 'TASK-1-1-03 Deliver and verify registration journey'
type: 'feature'
created: '2026-09-22'
status: 'done'
review_loop_iteration: 0
baseline_commit: 'a95b3bf3f8ff0ac5d9020ede3081e022c79b6682'
context:
  - '{project-root}/docs/contracts/auth/registration.md'
  - '{project-root}/docs/standards/testing.md'
---

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

**Problem:** The existing registration screen is only a visual scaffold: its API paths and response types do not match the cookie-based Laravel registration contract, auth state expects a bearer token, and server failures are not mapped to usable form feedback.

**Approach:** Complete the existing Vue registration journey around the approved same-origin Sanctum session contract, preserve the current visual design, and add focused unit/component and browser-ready verification for success, validation, duplicate email, CSRF/session recovery, throttling, accessibility, and protected-route behavior.

## Boundaries & Constraints

**Always:** Use PostgreSQL-backed API behavior through the existing Laravel contract; use HttpOnly session cookies and CSRF bootstrap; keep `PublicUser` limited to `id`, `name`, and `email`; use `/api/v1` API routes; expose field-level and actionable server errors; keep the existing auth layout and design language; add regression coverage for every material edge case.

**Ask First:** Any change to the backend registration contract, database schema, product copy, visual design system, or login behavior beyond adapting it to the shared cookie-session state.

**Never:** Store bearer tokens in browser storage or Pinia; add a second authentication mechanism; weaken password or email validation; bypass CSRF/throttle handling; mark the Story or unrelated tasks done; claim browser evidence without running the browser gate.

## I/O & Edge-Case Matrix

| Scenario | Input / State | Expected Output / Behavior | Error Handling |
|----------|---------------|----------------------------|----------------|
| HAPPY_PATH | Valid guest registration | CSRF is bootstrapped, account is created, user session is established, dashboard navigation occurs | No token is returned or stored |
| INVALID_FIELDS | Empty/invalid fields or password below backend policy | Submission is blocked with accessible field errors | No registration request is sent |
| DUPLICATE_EMAIL | Existing email | Form remains usable and shows server error associated with email when supplied | No authenticated state is created |
| CSRF_EXPIRED | Registration receives 419 | CSRF is refreshed and one bounded retry is attempted | Failure remains actionable; no retry loop |
| THROTTLED | Registration receives 429 | Form shows throttle message and retry timing when available | User is not silently retried |
| LOST_RESPONSE | Request outcome is unknown | Current account is reconciled before offering another submission | No duplicate account is created |

</frozen-after-approval>

## Code Map

- `apps/web/src/shared/api/client.ts` -- shared `ofetch` transport; owns API base URL, credentials, CSRF cookie/header handling, and normalized request errors.
- `apps/web/src/features/auth/api/auth.api.ts` -- registration, current-account, login, and logout endpoints; currently uses paths/types that do not match the contract.
- `apps/web/src/features/auth/api/auth.mutations.ts` -- TanStack Vue Query mutation lifecycle; currently stores a nonexistent bearer token and does not reconcile server errors.
- `apps/web/src/features/auth/stores/auth.store.ts` -- Pinia auth session state; must represent the cookie session without a token.
- `apps/web/src/features/auth/types/auth.types.ts` -- frontend auth DTOs; must match `PublicUser` and `{ data: { user } }` responses.
- `apps/web/src/features/auth/schemas/auth.schema.ts` -- VeeValidate/Zod rules; align password limits with the API contract.
- `apps/web/src/features/auth/components/RegisterForm.vue` -- existing registration UI; preserve styling while adding server-error mapping, retry/reconciliation state, and accessible associations.
- `apps/web/src/shared/components/molecules/FormField.vue` -- shared label/error wrapper; provide stable error IDs for `aria-describedby`.
- `apps/web/src/app/router/index.ts` -- protected-route guard is currently optimistic and must consult the cookie-backed session.
- `apps/web/vite.config.ts` -- local `/api` and `/sanctum` proxy is absent, preventing same-origin development transport.
- `apps/web/package.json` -- web verification scripts currently lack Vitest/Playwright entry points.
- `docs/contracts/auth/registration.md` -- canonical registration, CSRF, error, recovery, and response rules.
- `docs/standards/testing.md` -- required Vue unit/component and critical-journey browser verification conventions.

## Tasks & Acceptance

**Execution:**
- [x] `apps/web/src/shared/api/client.ts`, `apps/web/src/features/auth/api/auth.api.ts`, `apps/web/src/features/auth/types/auth.types.ts` -- align transport, CSRF bootstrap, normalized errors, endpoint paths, and response DTOs with the cookie-session contract.
- [x] `apps/web/src/features/auth/stores/auth.store.ts`, `apps/web/src/features/auth/api/auth.mutations.ts`, `apps/web/src/app/router/index.ts` -- remove token-based state, hydrate/reconcile the current account, and guard protected routes from the real session.
- [x] `apps/web/src/features/auth/schemas/auth.schema.ts`, `apps/web/src/features/auth/components/RegisterForm.vue`, `apps/web/src/shared/components/molecules/FormField.vue` -- enforce API-compatible validation and provide accessible client/server feedback for registration states.
- [x] `apps/web/vite.config.ts` -- proxy API and Sanctum paths during local development without changing production URLs.
- [x] `apps/web/tests/` and `apps/web/package.json` -- add focused unit/component coverage and a browser-ready registration smoke suite with explicit verification scripts.

**Acceptance Criteria:**
- Given a valid guest and available API, when registration is submitted, then CSRF bootstrap and `/api/v1/auth/register` complete with cookies, the returned `data.user` is stored, and the user reaches the dashboard without a bearer token.
- Given invalid client input, when the form is submitted, then no API mutation is sent and each invalid control exposes an associated accessible error.
- Given duplicate email, expired CSRF, throttling, or an unknown request outcome, when the API responds, then the UI presents the contract error and applies only the specified bounded recovery/reconciliation behavior.
- Given an unauthenticated cookie session, when a protected route is opened, then the router calls the current-account check and redirects to login on unauthenticated response.
- Given the web verification commands, when they run against the changed code, then type-check, lint/build, focused tests, and the available browser suite pass without weakening existing checks.

## Design Notes

The frontend may retry a 419 only once after a fresh CSRF bootstrap. A lost-response recovery must check `/api/v1/auth/me` before allowing another registration attempt; it must not infer success from a client timeout or create a client-side account record.

## Verification

**Commands:**
- `npm run type-check` -- expected: Vue/TypeScript compilation succeeds.
- `npm run lint` -- expected: ESLint and Oxlint succeed without generated fixes hiding errors.
- `npm run build` -- expected: production bundle succeeds.
- `npm run test:unit` -- expected: registration contract, validation, error, and session tests pass.
- `npm run test:e2e` -- expected: browser registration journey passes when the API/browser environment is available.

## Suggested Review Order

**Cookie session and recovery**

- Registration bootstraps CSRF, retries one expired session, and consumes the approved envelope.
  [`auth.api.ts:14`](../../apps/web/src/features/auth/api/auth.api.ts#L14)

- Shared transport sends credentials, reads CSRF cookies, and normalizes contract errors.
  [`client.ts:11`](../../apps/web/src/shared/api/client.ts#L11)

- Unknown registration outcomes reconcile through current-account before retry remains possible.
  [`auth.mutations.ts:22`](../../apps/web/src/features/auth/api/auth.mutations.ts#L22)

**Session and accessible UI**

- Protected routes now validate the cookie-backed current account instead of trusting scaffold state.
  [`index.ts:9`](../../apps/web/src/app/router/index.ts#L9)

- Registration form maps field errors, throttle/session messages, and accessible control associations.
  [`RegisterForm.vue:12`](../../apps/web/src/features/auth/components/RegisterForm.vue#L12)

- Shared fields expose stable error IDs for assistive technology.
  [`FormField.vue:1`](../../apps/web/src/shared/components/molecules/FormField.vue#L1)

**Verification and runtime**

- Contract, validation, and accessibility regressions are covered by focused tests.
  [`auth-registration.test.ts:1`](../../apps/web/tests/auth-registration.test.ts#L1)

- Browser smoke verifies the rendered registration journey and accessible fields.
  [`register.spec.ts:1`](../../apps/web/tests/e2e/register.spec.ts#L1)

- Local development proxies API and Sanctum paths without changing production URLs.
  [`vite.config.ts:8`](../../apps/web/vite.config.ts#L8)
