---
title: '5.3 Implement disposable retention and deletion safety controls'
type: 'feature'
created: '2026-10-08'
status: 'done'
review_loop_iteration: 0
baseline_commit: 'e27910f1e3f760ec87e6773c31310b955b195780'
context:
  - '{project-root}/_bmad-output/implementation-artifacts/epic-5-context.md'
  - '{project-root}/_bmad-output/planning-artifacts/epics/epic-5-operational-safety/stories/5-3-retain-and-delete-user-data-safely/requirements.md'
  - '{project-root}/_bmad-output/planning-artifacts/epics/epic-5-operational-safety/stories/5-3-retain-and-delete-user-data-safely/contract.md'
  - '{project-root}/apps/api/AGENTS.md'
---

## Intent

Provide a content-free retention/deletion policy evaluator and disposable
checkpoint runner. The MVP freezes data classes, dependency order, holds,
approval hashes, and resume semantics without mutating production or accepting
arbitrary User IDs, table names, stores, backups, or external processor scope.

## Boundaries

- Only `local-ci-disposable` and `dry_run=true` are accepted.
- Product records are represented by server-derived counts, never content.
- Holds preserve required history and pending external expiry classes.
- Checkpoints are in-memory, idempotent, tamper-detecting, and never claim
  production completion.
- Persistence, RBAC/API, scheduler, external processors, backups, and
  destructive production execution remain explicitly disabled.

## Tasks and acceptance

- [x] Implement the fixed policy evaluator and deterministic preview hash.
- [x] Implement checkpointed disposable execution with partial retry and exact
  resume position.
- [x] Reject arbitrary scope, wrong policy/environment, malformed inventories,
  tampered plans, and tampered checkpoint progress.
- [x] Verify holds, isolation boundary, no mutation, idempotent rerun, and
  `completion_claimable=false`.

## Verification

- `php artisan test --compact tests/Feature/OperationalSafety/RetentionPolicyEvaluatorTest.php`
- `vendor/bin/pint --dirty --format agent`
- `php -l app/Application/OperationalSafety/RetentionPolicyEvaluator.php`
- `git diff --check`

## Suggested Review Order

- Review fixed classes, actions, holds, and count-only plan construction.
  [`RetentionPolicyEvaluator.php:8`](../../apps/api/app/Application/OperationalSafety/RetentionPolicyEvaluator.php#L8)

- Review approval hash, checkpoint, failure injection, and idempotent resume.
  [`CheckpointedRetentionExecutor.php:8`](../../apps/api/app/Application/OperationalSafety/CheckpointedRetentionExecutor.php#L8)

- Walk policy, hold, tamper, checkpoint, and no-mutation tests.
  [`RetentionPolicyEvaluatorTest.php:13`](../../apps/api/tests/Feature/OperationalSafety/RetentionPolicyEvaluatorTest.php#L13)
