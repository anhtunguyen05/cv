# Story 4.1: Start an Evidence interview — Tasks

## Tasks & Acceptance

**Execution:**

- [ ] TASK-4-1-01: Freeze Interview start contract fixtures
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `none`
  - Covers: `AC-4-1-start-an-evidence-interview-01`, `AC-4-1-start-an-evidence-interview-02`
  - Scope: 01. Freeze Interview eligibility, source, question, and API fixtures: eligible/not-needed/source/session/retry/concurrency/question payloads
  - Coordination: `E4-COORD-INTERVIEW-001`, `E4-COORD-TEST-001`
  - Blocked by: `E4-PREREQ-VERSION-001`; `E4-PREREQ-MATCH-001`; `E4-DEC-001`; `E4-DEC-002`; `E4-DEC-009`
  - Outcome: 01. Freeze Interview eligibility, source, question, and API fixtures: One accepted start/read contract for backend and frontend.
  - Acceptance: 01. Freeze Interview eligibility, source, question, and API fixtures: fixtures pin full source tuple and prove no misleading empty session.
  - Verification: 01. Freeze Interview eligibility, source, question, and API fixtures: schema/fixture and product/architecture/UX approval evidence.

- [ ] TASK-4-1-02: Deliver eligibility-guarded Interview start backend
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-4-1-01`
  - Covers: `AC-4-1-start-an-evidence-interview-01`, `AC-4-1-start-an-evidence-interview-02`
  - Scope: 01. Implement Interview aggregate and persistence constraints: ULID model/migration, source/owner/area/question-set/status fields, uniqueness/indexes | 02. Implement Interview eligibility and atomic start service: ownership graph, unresolved-area resolution, question snapshot, dedupe/precondition/transaction | 03. Expose Interview start/read APIs: Form Request, policy, resource/controller/routes, envelope/error/allowed-action mapping | 04. Verify source integrity, ownership, and start races: two-User, source mutation assertions, active duplicate, concurrency, rollback, safe errors
  - Coordination: `E4-COORD-INTERVIEW-001`, `E4-COORD-TEST-001`
  - Blocked by: `E4-DEC-009`
  - Outcome: 01. Implement Interview aggregate and persistence constraints: Persist one identifiable session without mutable source/area drift. | 02. Implement Interview eligibility and atomic start service: Create/reconcile only eligible sessions and leave sources unchanged. | 03. Expose Interview start/read APIs: Serve approved non-disclosing start/read behavior. | 04. Verify source integrity, ownership, and start races: Prove start is isolated, atomic, and source-preserving.
  - Acceptance: 01. Implement Interview aggregate and persistence constraints: PostgreSQL constraints reject incomplete/cross-source/duplicate trusted state. | 02. Implement Interview eligibility and atomic start service: no-area, foreign, stale, concurrent, rollback, and lost-response cases match fixtures. | 03. Expose Interview start/read APIs: transport/auth/eligibility/idempotency responses match canonical fixtures. | 04. Verify source integrity, ownership, and start races: exact fixture markers remain unchanged and only one approved session outcome exists.
  - Verification: 01. Implement Interview aggregate and persistence constraints: migration/model/PostgreSQL constraint tests. | 02. Implement Interview eligibility and atomic start service: domain/application/PostgreSQL concurrency and rollback tests. | 03. Expose Interview start/read APIs: Laravel feature/contract tests with two Users. | 04. Verify source integrity, ownership, and start races: approved PHPUnit/PostgreSQL/Vitest commands and evidence.

- [ ] TASK-4-1-03: Deliver and verify Interview start journey
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-4-1-01`
  - Covers: `AC-4-1-start-an-evidence-interview-01`, `AC-4-1-start-an-evidence-interview-02`
  - Scope: 01. Build Interview start and not-needed frontend states: schema/adapter/query/mutation, start control, areas, loading/not-needed/conflict/error/recovery/focus | 02. Verify Interview start journey end to end: browser eligible/not-needed/reload/retry/foreign/keyboard scenarios
  - Coordination: `E4-COORD-INTERVIEW-001`, `E4-COORD-TEST-001`
  - Blocked by: `E4-DEC-009`; approved E4-COORD-INTERVIEW-001 answer-entry checkpoint from Story 4.2
  - Outcome: 01. Build Interview start and not-needed frontend states: Explain eligibility and enter the exact session accessibly. | 02. Verify Interview start journey end to end: Verify the complete Match Report-to-Interview handoff.
  - Acceptance: 01. Build Interview start and not-needed frontend states: duplicate clicks are guarded and no-area state cannot imply an interview exists. | 02. Verify Interview start journey end to end: scenarios pass independently with disposable synthetic source data. | Integrated journey acceptance closes only after `TASK-4-1-02` is done with backend evidence.
  - Verification: 01. Build Interview start and not-needed frontend states: type-check, adapter, component, keyboard, and accessibility tests. | 02. Verify Interview start journey end to end: approved Playwright command and fixture/reset evidence. | Run the cross-layer journey check after `TASK-4-1-02` passes its backend acceptance.

## Dependency and concurrency map
- `TASK-4-1-01` depends on `none`.
- `TASK-4-1-02` depends on `TASK-4-1-01`.
- `TASK-4-1-03` depends on `TASK-4-1-01`; its frontend or evidence work can proceed alongside `TASK-4-1-02`, and integrated acceptance closes after `TASK-4-1-02` is done.


The start flow consumes the approved Match Report source context; Story 4.2 owns answer-entry persistence and progression.
