# Epic 1 Test Strategy

## Verification principles

- **E1-TEST-001:** Every canonical AC is mapped to at least one task and one
  verification layer; task completion never replaces system-level AC evidence.
- **E1-TEST-002:** Account/domain rules use PHPUnit unit tests; routes,
  sessions, policies, transactions, validation, and serialization use Laravel
  feature tests.
- **E1-TEST-003:** Migrations, uniqueness, ownership constraints, JSON/schema
  compatibility, concurrency, and Version immutability are verified on a
  declared disposable MySQL 8.4 database.
- **E1-TEST-004:** Vue schemas, adapters, composables/stores, editor components,
  nested errors, and interaction states use Vitest.
- **E1-TEST-005:** Playwright covers registration/current-account, sign-in/out
  and expiry, Profile create/reload, section edit/reload, cross-user denial,
  and Version create/reload/immutability on disposable data.
- **E1-TEST-006:** Contract fixtures are shared between backend, frontend, and
  E2E consumers; each fixture names its rule/contract version and expected
  status/code/field paths.

## Story coverage matrix

| Story | Unit/component | API/integration | Browser critical path |
| --- | --- | --- | --- |
| 1.1 Register | identity policy, form states | duplicate race, transaction, session, redaction | register then current account |
| 1.2 Sign in/out | auth state and generic error | session regeneration/invalidation/expiry | sign in, protected route, sign out |
| 1.3 Profile create | aggregate validation, form | ownership, atomic create, reload | create and reload Profile |
| 1.4 Summary/skills | schema and editor | structured persistence, immutable Version regression | edit/reload summary and skills |
| 1.5 Education/experience | item rules and editor | stable IDs, add/edit/remove atomicity | edit repeated entries |
| 1.6 Projects | nested technology/bullet rules | malformed nested data and ownership | edit project Evidence |
| 1.7 Supplementary | optional-section rules | preserve existing entries | empty and populated sections |
| 1.8 Version | snapshot service and list state | MySQL transaction, ordering, ownership, immutability | create, reload, edit Profile, re-open Version |

## Integration gates

1. Shared frontend Vitest and Playwright enablement is delivered as one
   project-level enablement item, not duplicated in story tasks.
2. `E1-CONTRACT-AUTH-001` and its fixtures are frozen before Stories 1.1/1.2
   frontend/backend work diverges.
3. `E1-CONTRACT-PROFILE-001` and nested field-path fixtures are frozen before
   Stories 1.3 through 1.7 proceed in parallel.
4. `E1-CONTRACT-VERSION-001` is frozen after the complete Profile schema and
   before Story 1.8 implementation.
5. Full Epic acceptance runs API tests, MySQL integration tests, web static and
   component checks, and Playwright against explicitly disposable data.
