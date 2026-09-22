# Story 4.2: Answer Evidence questions — Tasks

## Tasks & Acceptance

**Execution:**

- [ ] TASK-4-2-01: Freeze Evidence answer and progression fixtures
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `none`
  - Covers: `AC-4-2-answer-evidence-questions-01`, `AC-4-2-answer-evidence-questions-02`, `AC-4-2-answer-evidence-questions-03`
  - Scope: 01. Freeze Evidence answer, provenance, progression, and error fixtures: valid/invalid/cannot-provide/stale/retry/correction/provenance/progress payloads
  - Coordination: `E4-COORD-INTERVIEW-001`, `E4-COORD-TEST-001`
  - Blocked by: `E4-DEC-001`; `E4-DEC-002`; `E4-DEC-003`; `E4-DEC-009`; approved E4-COORD-INTERVIEW-001 session/question checkpoint from Story 4.1
  - Outcome: 01. Freeze Evidence answer, provenance, progression, and error fixtures: Freeze one answer/progress contract shared across layers.
  - Acceptance: 01. Freeze Evidence answer, provenance, progression, and error fixtures: fixtures distinguish User text, non-supporting outcome, and system content.
  - Verification: 01. Freeze Evidence answer, provenance, progression, and error fixtures: schema/fixture and product/architecture/UX approval evidence.

- [ ] TASK-4-2-02: Deliver provenance-safe Evidence answer backend
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-4-2-01`
  - Covers: `AC-4-2-answer-evidence-questions-01`, `AC-4-2-answer-evidence-questions-02`, `AC-4-2-answer-evidence-questions-03`
  - Scope: 01. Implement Evidence Answer persistence and provenance: ULID/model/migration, raw/normalized data, mode, question/session/source, timestamp, provenance, indexes | 02. Implement answer validation and atomic session transition: active/current question, mode/text rules, stale/dedupe/concurrency, next/completion transition | 03. Expose Evidence answer/progress APIs: Form Request, policy, resources/controllers/routes, envelope/error/allowed-action mapping | 04. Verify Evidence integrity, isolation, and races: two-User, raw/provenance, non-supporting, stale/out-of-order, duplicate/lost/concurrent, logs
  - Coordination: `E4-COORD-INTERVIEW-001`, `E4-COORD-TEST-001`
  - Blocked by: `E4-DEC-009`
  - Outcome: 01. Implement Evidence Answer persistence and provenance: Persist attributable Evidence without erasing original answers. | 02. Implement answer validation and atomic session transition: Accept exactly one valid User outcome and advance consistently. | 03. Expose Evidence answer/progress APIs: Serve safe answer and current progress contracts. | 04. Verify Evidence integrity, isolation, and races: Prove answers remain attributable, safe, and transactionally consistent.
  - Acceptance: 01. Implement Evidence Answer persistence and provenance: constraints reject ambiguous mode/source/provenance and preserve correction history as approved. | 02. Implement answer validation and atomic session transition: invalid/negative/retry/race cases never become false supporting Evidence. | 03. Expose Evidence answer/progress APIs: auth, validation, stale, duplicate, malformed, and success match fixtures. | 04. Verify Evidence integrity, isolation, and races: exact markers and timestamps prove no invented/foreign/ambiguous Evidence.
  - Verification: 01. Implement Evidence Answer persistence and provenance: migration/model/PostgreSQL constraint tests. | 02. Implement answer validation and atomic session transition: domain/application/PostgreSQL concurrency/rollback tests. | 03. Expose Evidence answer/progress APIs: Laravel feature/contract tests with two Users. | 04. Verify Evidence integrity, isolation, and races: approved PHPUnit/PostgreSQL/Vitest/security commands and evidence.

- [ ] TASK-4-2-03: Deliver and verify Evidence answering journey
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-4-2-01`
  - Covers: `AC-4-2-answer-evidence-questions-01`, `AC-4-2-answer-evidence-questions-02`, `AC-4-2-answer-evidence-questions-03`
  - Scope: 01. Build accessible Evidence question and answer flow: adapter/state/form, answer/cannot-provide, draft, limits, submit/progress/conflict/reload/focus | 02. Verify Evidence answering journey end to end: browser answer/invalid/decline/reload/conflict/retry/foreign/keyboard scenarios
  - Coordination: `E4-COORD-INTERVIEW-001`, `E4-COORD-TEST-001`
  - Blocked by: `E4-DEC-009`
  - Outcome: 01. Build accessible Evidence question and answer flow: Let Users provide or decline Evidence with clear provenance and recovery. | 02. Verify Evidence answering journey end to end: Verify the complete Evidence collection journey.
  - Acceptance: 01. Build accessible Evidence question and answer flow: no mode ambiguity, errors preserve input, and progress/status is accessible. | 02. Verify Evidence answering journey end to end: independent disposable-data scenarios preserve User versus system provenance. | Integrated journey acceptance closes only after `TASK-4-2-02` is done with backend evidence.
  - Verification: 01. Build accessible Evidence question and answer flow: type-check, adapter/component, keyboard, and accessibility tests. | 02. Verify Evidence answering journey end to end: approved Playwright command and fixture/reset evidence. | Run the cross-layer journey check after `TASK-4-2-02` passes its backend acceptance.

## Dependency and concurrency map
- `TASK-4-2-01` depends on `none`.
- `TASK-4-2-02` depends on `TASK-4-2-01`.
- `TASK-4-2-03` depends on `TASK-4-2-01`; its frontend or evidence work can proceed alongside `TASK-4-2-02`, and integrated acceptance closes after `TASK-4-2-02` is done.


The answer flow consumes the pinned Interview session and questions from Story 4.1; Story 4.2 owns User-provided answer persistence and provenance.
