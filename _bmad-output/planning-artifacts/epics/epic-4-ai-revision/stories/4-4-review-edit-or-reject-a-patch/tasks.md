# Story 4.4: Review, edit, or reject a Patch — Tasks

## Tasks & Acceptance

**Execution:**

- [ ] TASK-4-4-01: Freeze Patch review and decision fixtures
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `none`
  - Covers: `AC-4-4-review-edit-or-reject-a-patch-01`, `AC-4-4-review-edit-or-reject-a-patch-02`, `AC-4-4-review-edit-or-reject-a-patch-03`
  - Scope: 01. Freeze Patch review, edit, reject, and conflict fixtures: diff/provenance/allowed-action/edit/status/confirm/race/error/accessibility payloads
  - Coordination: `E4-COORD-PATCH-001`, `E4-COORD-TEST-001`
  - Blocked by: `E4-DEC-001`; `E4-DEC-004`; `E4-DEC-007`; `E4-DEC-008`; `E4-DEC-009`; approved E4-COORD-PATCH-001 valid-pending checkpoint from Story 4.3
  - Outcome: 01. Freeze Patch review, edit, reject, and conflict fixtures: Freeze one human-decision contract across backend and frontend.
  - Acceptance: 01. Freeze Patch review, edit, reject, and conflict fixtures: fixtures separate source, provider proposal, User edit, status, and Evidence provenance.
  - Verification: 01. Freeze Patch review, edit, reject, and conflict fixtures: schema/fixture and product/architecture/UX approval evidence.

- [ ] TASK-4-4-02: Deliver Patch review, edit, and rejection backend
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-4-4-01`
  - Covers: `AC-4-4-review-edit-or-reject-a-patch-01`, `AC-4-4-review-edit-or-reject-a-patch-02`, `AC-4-4-review-edit-or-reject-a-patch-03`
  - Scope: 01. Implement owned Patch read projection and read API: ownership graph, exact source/diff/Evidence/provenance/status/action query/resource/controller/route | 02. Implement bounded Patch edit and revalidation transition: allowed new value, target lock, provenance/revision, validation, stale/idempotency/concurrency transaction | 03. Implement explicit Patch rejection transition: confirmation contract, status guard, decision provenance/time, idempotency/concurrency transaction | 04. Expose Patch edit and reject APIs: Form Requests, policies, edit/reject controllers/resources/routes, precondition/idempotency/error mapping | 05. Verify Patch decisions, isolation, and state races: two-User, source mutation, provenance, unsafe content, stale/repeat/concurrent edit/reject/approve, logs
  - Coordination: `E4-COORD-PATCH-001`, `E4-COORD-TEST-001`
  - Blocked by: `E4-DEC-009`
  - Outcome: 01. Implement owned Patch read projection and read API: Return a safe truthful review projection without recomputation. | 02. Implement bounded Patch edit and revalidation transition: Preserve an attributable valid User-edited proposal without source mutation. | 03. Implement explicit Patch rejection transition: Record one immutable User rejection and preserve source/proposal history. | 04. Expose Patch edit and reject APIs: Serve safe bounded edit and explicit reject transition contracts. | 05. Verify Patch decisions, isolation, and state races: Prove decisions are authorized, attributable, and consistent.
  - Acceptance: 01. Implement owned Patch read projection and read API: foreign/missing/invalid-schema Patch leaks no nested data and actions reflect status. | 02. Implement bounded Patch edit and revalidation transition: invalid/out-of-target/stale/repeated/racing edits create no ambiguous trusted state. | 03. Implement explicit Patch rejection transition: repeat same reject reconciles; competing edit/approve/reject has one winner. | 04. Expose Patch edit and reject APIs: validation/status/stale/repeat/concurrent/foreign cases match fixtures. | 05. Verify Patch decisions, isolation, and state races: exact source markers remain unchanged and every race has one approved outcome.
  - Verification: 01. Implement owned Patch read projection and read API: domain/Laravel contract tests with two Users. | 02. Implement bounded Patch edit and revalidation transition: unit/application/MySQL concurrency/rollback tests. | 03. Implement explicit Patch rejection transition: domain/Laravel/MySQL race and rollback tests. | 04. Expose Patch edit and reject APIs: Laravel feature/contract tests with two Users. | 05. Verify Patch decisions, isolation, and state races: approved PHPUnit/MySQL/Vitest/security commands and evidence.

- [ ] TASK-4-4-03: Deliver and verify Patch decision experience
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-4-4-01`
  - Covers: `AC-4-4-review-edit-or-reject-a-patch-01`, `AC-4-4-review-edit-or-reject-a-patch-02`, `AC-4-4-review-edit-or-reject-a-patch-03`
  - Scope: 01. Build accessible Patch review, edit, and reject UI: adapter/query/mutations, semantic diff/provenance, edit validation, reject confirm, status/conflict/error/focus | 02. Verify Patch review and rejection journey end to end: browser open/edit/invalid/reject/reload/conflict/foreign/keyboard scenarios
  - Coordination: `E4-COORD-PATCH-001`, `E4-COORD-TEST-001`
  - Blocked by: `E4-DEC-009`
  - Outcome: 01. Build accessible Patch review, edit, and reject UI: Keep human control and proposal/source distinction understandable. | 02. Verify Patch review and rejection journey end to end: Verify the complete human review/edit/reject flow.
  - Acceptance: 01. Build accessible Patch review, edit, and reject UI: unsafe text is inert, allowed actions drive controls, and errors preserve review/edit context. | 02. Verify Patch review and rejection journey end to end: disposable scenarios preserve source/proposal/history and accessible recovery. | Integrated journey acceptance closes only after `TASK-4-4-02` is done with backend evidence.
  - Verification: 01. Build accessible Patch review, edit, and reject UI: type-check, adapter/component, keyboard, and accessibility tests. | 02. Verify Patch review and rejection journey end to end: approved Playwright command and fixture/reset evidence. | Run the cross-layer journey check after `TASK-4-4-02` passes its backend acceptance.

## Dependency and concurrency map
- `TASK-4-4-01` depends on `none`.
- `TASK-4-4-02` depends on `TASK-4-4-01`.
- `TASK-4-4-03` depends on `TASK-4-4-01`; its frontend or evidence work can proceed alongside `TASK-4-4-02`, and integrated acceptance closes after `TASK-4-4-02` is done.


Keep shared Patch review/decision files under the existing coordination records. Reserve non-overlapping scopes before work proceeds.
