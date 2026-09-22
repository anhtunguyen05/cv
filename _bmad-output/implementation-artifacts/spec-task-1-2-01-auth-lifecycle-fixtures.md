---
title: 'TASK-1-2-01 Freeze Authentication Lifecycle Fixtures'
type: 'feature'
created: '2026-09-22'
status: 'done'
baseline_commit: '8e4c40fd6479466f05e5d00115c49c0d416546ce'
review_loop_iteration: 0
context:
  - '{project-root}/_bmad-output/planning-artifacts/epics/epic-1-trusted-cv/stories/1-2-sign-in-and-sign-out/README.md'
  - '{project-root}/_bmad-output/planning-artifacts/epics/epic-1-trusted-cv/stories/1-2-sign-in-and-sign-out/tasks.md'
  - '{project-root}/docs/contracts/auth/registration.md'
  - '{project-root}/docs/contracts/auth/fixtures/registration-v1.json'
  - '{project-root}/docs/standards/testing.md'
---

<frozen-after-approval reason="human-owned intent - do not modify unless human renegotiates">

## Intent

**Problem:** Story 1.2 has no executable shared contract for sign-in, sign-out,
session expiry, and stale protected-state recovery. Without one corpus, the
backend and frontend can silently implement different authentication behavior.

**Approach:** Extend the existing registration/current-account contract and
fixture corpus through an explicit revision. Preserve all Story 1.1 rows and
add login, logout, limiter, redaction, expiry, and recovery scenarios consumed
by later backend and frontend tasks.

## Boundaries & Constraints

**Always:** Keep the existing canonical paths; use `/api/v1/auth/login`,
`/api/v1/auth/me`, and `/api/v1/auth/logout`; use Sanctum cookie sessions and
CSRF; serialize only `id`, `name`, and `email`; use generic invalid-credential
responses; use hashed IP/email limiter keys with 5 attempts/10 minutes per IP
and 3 attempts/10 minutes per canonical email; return `204` for repeated logout
when the CSRF boundary is valid; map expiry to protected-state clearing before
safe internal login redirect; keep fixtures synthetic and database-neutral.

**Ask First:** Stop if the approved decision records contradict these values or
if a consumer requires a second auth corpus rather than the canonical revision.

**Never:** Implement Laravel/Vue behavior, add bearer-token authentication,
store credentials in browser state, create local fixture variants, or change
Story 1.1 registration semantics.

## I/O & Edge-Case Matrix

| Scenario | Input / State | Expected Output / Behavior | Error Handling |
|---|---|---|---|
| Valid login | Guest, valid CSRF and credentials | `200`, public User, regenerated authenticated session | No forbidden fields |
| Invalid login | Unknown email or wrong password | Identical `401 INVALID_CREDENTIALS` shape | No account enumeration |
| Throttled login | IP/email limit exceeded | `429 THROTTLED` plus `Retry-After` | No automatic retry |
| Logout | Authenticated or already guest, valid CSRF | `204`, session invalidated, User data unchanged | Later protected request is rejected |
| Expiry | Protected request has `401` or `419` | Clear protected cache/auth, redirect to safe internal login path | Stale response cannot restore state |

</frozen-after-approval>

## Code Map

- `_bmad-output/planning-artifacts/epics/epic-1-trusted-cv/stories/1-2-sign-in-and-sign-out/tasks.md` -- task scope, dependency, AC, and evidence contract.
- `_bmad-output/planning-artifacts/epics/epic-1-trusted-cv/decisions.md` -- approved auth topology, navigation behavior, and endpoint boundary.
- `docs/contracts/auth/registration.md` -- canonical auth contract document to revise without duplicating global standards.
- `docs/contracts/auth/fixtures/registration-v1.json` -- sole executable cross-layer fixture corpus; preserve existing registration rows.
- `docs/contracts/common/http.md` -- shared success/failure envelopes and framework error codes.
- `docs/standards/{security,validation,testing}.md` -- credential hygiene, stable validation, and required evidence layers.
- `apps/api/routes/api.php` and `apps/api/tests/Feature/Auth/RegistrationTest.php` -- read-only evidence of the current API boundary and existing fixture-consumer patterns; login/logout are not yet implemented.
- `apps/web/src/features/auth/{api,stores}` and `apps/web/src/app/router/index.ts` -- read-only evidence of current client adapters, auth state, and protected-route recovery.

## Tasks & Acceptance

**Execution:**
- [x] `docs/contracts/auth/registration.md` -- revise the document as the auth lifecycle contract and record the fixture revision -- make login/logout/expiry authoritative without a second contract.
- [x] `docs/contracts/auth/fixtures/registration-v1.json` -- add login, logout, limiter, redaction, expiry, and stale-response rows -- give backend/frontend tasks one executable source.
- [x] `tasks.md` -- record the owner, branch/worktree, exact policy resolutions, and verification evidence for TASK-1-2-01 -- make the task independently assignable and auditable.

**Acceptance Criteria:**
- Given the revised corpus, when it is parsed and structurally inspected, then every Story 1.2 AC has a uniquely identified row with request/precondition/expectation and cross-reference metadata.
- Given unknown-email and wrong-password rows, when their public failure expectations are compared, then status, code, message, and response shape are identical.
- Given successful logout rows, when later protected access is evaluated, then the session is invalidated, protected state is cleared, and User-owned data remains unchanged.
- Given expiry or stale-response rows, when a protected request fails, then the client clears protected state before redirect and cannot repopulate it from an older response.
- Given any public User fixture, when forbidden-field assertions run, then only `id`, `name`, and `email` are allowed.

## Design Notes

Retain the reserved canonical file paths and represent the revision in the
fixture metadata; do not rename the corpus without updating Story 1.1 and the
coordination record. HTTP behavior belongs in `expect`; client ordering and
cache effects belong in `client_action`/state assertions so later consumers do
not infer UI behavior from HTTP status alone.

## Verification

**Commands:**
- `node -e "const fs=require('fs'); const f=JSON.parse(fs.readFileSync('docs/contracts/auth/fixtures/registration-v1.json','utf8')); const ids=f.fixtures.map(x=>x.id); if(new Set(ids).size!==ids.length) throw new Error('duplicate fixture id'); const required=['csrf_bootstrap','register','login','current_account','logout','protected_request']; for(const op of required) if(!f.fixtures.some(x=>x.operation===op)) throw new Error('missing operation '+op); const prefix='AC-1-2-sign-in-and-sign-out-'; for(let i=1;i<=5;i++) if(!f.fixtures.some(x=>(x.ac||[]).includes(prefix+String(i).padStart(2,'0')))) throw new Error('missing AC '+i); const row=id=>f.fixtures.find(x=>x.id===id); for(const id of ids.filter(id=>id.startsWith('login-')||id.startsWith('logout-')||id.startsWith('protected-')||id==='stale-response-after-auth-clear')) { const r=row(id); for(const key of ['operation','precondition','request','expect','ac','references']) if(r[key]===undefined) throw new Error(id+' missing '+key); } const unknown=row('login-unknown-email').expect.error; const wrong=row('login-wrong-password').expect.error; if(JSON.stringify(unknown)!==JSON.stringify(wrong)) throw new Error('invalid login failures differ'); if(row('login-wrong-password').expect.same_public_failure_as!=='login-unknown-email') throw new Error('missing login equivalence'); if(row('logout-already-invalidated').expect.status!==204 || row('logout-already-invalidated').expect.idempotent!==true) throw new Error('logout idempotency missing'); if(JSON.stringify(row('protected-request-expired-session').expect.client_action_order)!==JSON.stringify(['clear_protected_query_data','clear_auth_state','discard_stale_response','redirect_to_safe_internal_login'])) throw new Error('expiry order mismatch'); if(Object.keys(f.public_user).sort().join(',')!=='email,id,name') throw new Error('bad public user'); if(f.forbidden_response_fields.some(k=>JSON.stringify(f.public_user).includes(k))) throw new Error('forbidden public field'); console.log('auth-lifecycle v2: 24 unique rows; semantic and structural audit passed')"` -- passed.
- `git diff --check` -- passed with no whitespace errors.

**Manual checks:**
- Confirm all existing Story 1.1 rows remain semantically unchanged.
- Confirm no real credential, hash, cookie, CSRF value, bearer token, or local contract variant is introduced.
- Confirm the revised document, fixture, and task references agree on revision, limiter, logout idempotency, and AC coverage.

## Suggested Review Order

**Contract boundary**

- Review the revised lifecycle ownership and compatibility revision first.
  [`registration.md:1`](../../docs/contracts/auth/registration.md#L1)

- Confirm session, CSRF, and public-user invariants remain explicit.
  [`registration.md:17`](../../docs/contracts/auth/registration.md#L17)

- Check operation statuses and failure classifications before reading rows.
  [`registration.md:48`](../../docs/contracts/auth/registration.md#L48)

**Executable scenarios**

- Verify revision metadata and preserved public projection.
  [`registration-v1.json:1`](../../docs/contracts/auth/fixtures/registration-v1.json#L1)

- Compare unknown-email and wrong-password equivalence rows.
  [`registration-v1.json:161`](../../docs/contracts/auth/fixtures/registration-v1.json#L161)

- Verify malformed input and limiter coverage.
  [`registration-v1.json:186`](../../docs/contracts/auth/fixtures/registration-v1.json#L186)

- Verify logout idempotency and CSRF failure behavior.
  [`registration-v1.json:216`](../../docs/contracts/auth/fixtures/registration-v1.json#L216)

- Verify expiry ordering and stale-response suppression.
  [`registration-v1.json:246`](../../docs/contracts/auth/fixtures/registration-v1.json#L246)

**Traceability and lifecycle**

- Confirm owner, scope, evidence, and downstream boundaries.
  [`tasks.md:9`](../planning-artifacts/epics/epic-1-trusted-cv/stories/1-2-sign-in-and-sign-out/tasks.md#L9)

- Confirm the story lifecycle moved to review without changing other stories.
  [`sprint-status.yaml:16`](sprint-status.yaml#L16)
