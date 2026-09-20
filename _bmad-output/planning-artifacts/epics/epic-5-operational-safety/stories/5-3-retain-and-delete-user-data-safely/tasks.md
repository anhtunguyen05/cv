# Story 5.3 — Tasks

## Tasks & Acceptance

**Execution:**

- [ ] TASK-5-3-01: Inventory data and freeze retention/deletion policy
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `none`
  - Covers: `AC-5-3-retain-and-delete-user-data-safely-01`, `AC-5-3-retain-and-delete-user-data-safely-02`
  - Scope: 01. Inventory and classify User, derived, operational, cache, backup, and external data: data classes/stores/owners/links/authority/clock/action/holds/backups/processors/verification map | 02. Freeze retention/deletion policy, API, dry-run, and failure fixtures: cutoff/action/order/hold/request/confirm/status/batch/partial/rerun/audit/isolation payloads
  - Coordination: `E5-COORD-RETENTION-001`, `E5-COORD-TEST-001`
  - Blocked by: `E5-DEC-004`; `E5-DEC-001`; `E5-DEC-002`; `E5-DEC-008`; `DISCOVERY-E5-001`
  - Outcome: 01. Inventory and classify User, derived, operational, cache, backup, and external data: Complete approved dependency inventory before destructive design. | 02. Freeze retention/deletion policy, API, dry-run, and failure fixtures: One approved executable policy and safe state contract.
  - Acceptance: 01. Inventory and classify User, derived, operational, cache, backup, and external data: every known source/derived/operational copy has one disposition and proof method. | 02. Freeze retention/deletion policy, API, dry-run, and failure fixtures: fixtures cover every inventory row and destructive control.
  - Verification: 01. Inventory and classify User, derived, operational, cache, backup, and external data: architecture/security/legal/data-owner review evidence. | 02. Freeze retention/deletion policy, API, dry-run, and failure fixtures: policy/schema/threat/legal/UX approval evidence.

- [ ] TASK-5-3-02: Deliver policy-scoped request and preview control plane
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-5-3-01`
  - Covers: `AC-5-3-retain-and-delete-user-data-safely-01`, `AC-5-3-retain-and-delete-user-data-safely-02`
  - Scope: 01. Implement the policy evaluator, dry-run preview, and scoped request lifecycle: cutoff/eligibility/hold/dependency plan, server-derived subject, approval hash, and request/status/checkpoint persistence | 02. Expose authorized retention/deletion preview, request, confirmation, and status APIs: User/operator policies, validated requests/resources/errors, and no arbitrary identifiers | 03. Build accessible deletion controls and status experience: explicit scope confirmation, dry-run, progress/partial/retained/completed/error/retry states, adapter behavior, keyboard access, and focus handling
  - Coordination: `E5-COORD-RETENTION-001`, `E5-COORD-AUDIT-001`, `E5-COORD-TEST-001`
  - Blocked by: `E5-DEC-001`; `DISCOVERY-E5-001`; `E5-DEC-008`
  - Outcome: 01. Implement the policy evaluator, dry-run preview, and scoped request lifecycle: Produce an approved mutation-free plan tied to the requesting User and exact policy. | 02. Expose authorized retention/deletion APIs: Serve bounded, reviewable operations without arbitrary identifiers. | 03. Build accessible deletion controls and status experience: Explain the scope and report honest progress, partial, retained, completed, and recovery states.
  - Acceptance: 01. Implement the policy evaluator, dry-run preview, and scoped request lifecycle: stale policy/preview/environment and unauthorized scope cannot execute or mutate data. | 02. Expose authorized retention/deletion APIs: authorization, confirmation, exact policy/version, partial and status responses match approved fixtures. | 03. Build accessible deletion controls and status experience: no false completion or arbitrary scope; retained/partial states offer clear next actions and keyboard users can confirm/recover safely.
  - Verification: 01. Implement the policy evaluator, dry-run preview, and scoped request lifecycle: unit/Laravel/MySQL policy, RBAC, idempotency, and no-mutation tests. | 02. Expose authorized retention/deletion APIs: Laravel feature/contract/RBAC/idempotency tests across two Users and malformed/partial states. | 03. Build accessible deletion controls and status experience: type-check, adapter/component, keyboard/accessibility tests and explicit confirm/recovery review.

- [ ] TASK-5-3-03: Deliver checkpointed retention execution and safe scheduled runner
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-5-3-01`
  - Covers: `AC-5-3-retain-and-delete-user-data-safely-01`, `AC-5-3-retain-and-delete-user-data-safely-02`
  - Scope: 01. Implement the checkpointed retention/deletion executor and adapters: dependency batches, locks, delete/anonymize/aggregate, caches/storage/external fakes, partial/rerun, and safe audit | 02. Implement the controlled retention scheduler and runner: approved schedule/manual trigger, environment/policy/version guard, single-run lease, dry-run/approval, batch/checkpoint/resume, and safe outcome | 03. Verify isolation, consistency, recovery, and non-sensitive audit: two Users, full dependency graph, holds, concurrent writes, partial/rerun, backup/external policy, and canaries
  - Coordination: `E5-COORD-RETENTION-001`, `E5-COORD-TEST-001`
  - Blocked by: `E5-DEC-001`; `DISCOVERY-E5-001`; `E5-DEC-008`; approved policy-evaluator and request/API handoff checkpoint from `TASK-5-3-02`; production execution and enablement remain blocked by `DISCOVERY-E5-003` until separately approved
  - Outcome: 01. Implement the checkpointed retention/deletion executor and adapters: Execute only approved scope and resume safely after failure. | 02. Implement the controlled retention scheduler and runner: Process due work once under explicit environment and policy-version controls. | 03. Verify isolation, consistency, recovery, and non-sensitive audit: Prove scoped outcomes and safe recovery without retaining content.
  - Acceptance: 01. Implement the checkpointed retention/deletion executor and adapters: unrelated User data and retained trusted invariants remain unchanged in every injected failure. | 02. Implement the controlled retention scheduler and runner: overlapping/stale/wrong-environment runs cannot execute and partial work resumes from the exact checkpoint. Production execution and enablement remain blocked by `DISCOVERY-E5-003` until separately approved. | 03. Verify isolation, consistency, recovery, and non-sensitive audit: pre/post evidence proves target disposition, unrelated data unchanged, and retained records consistent. | Integrated user and scheduled journeys close only after the full `TASK-5-3-02` acceptance, including accessible deletion controls and status states.
  - Verification: 01. Implement the checkpointed retention/deletion executor and adapters: disposable MySQL/storage/external-fake integration, race, fault, and rerun tests. | 02. Implement the controlled retention scheduler and runner: fake-clock/scheduler/lease/disposable-runner integration tests in dry-run or approved disposable context. | 03. Verify isolation, consistency, recovery, and non-sensitive audit: approved disposable-environment scenarios plus Playwright/retention-runner evidence; no production execution without `DISCOVERY-E5-003`.

## Dependency and concurrency map
- `TASK-5-3-01` depends on `none`.
- `TASK-5-3-02` depends on `TASK-5-3-01`.
- `TASK-5-3-03` depends on `TASK-5-3-01`; executor and scheduler work is blocked until the policy-evaluator and request/API handoff checkpoint in `TASK-5-3-02` is accepted, then may proceed alongside its UI work; integrated journey closure waits for the full `TASK-5-3-02` acceptance.
