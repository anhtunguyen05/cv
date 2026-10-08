---
title: '5.2 Deliver bounded synchronous provider recovery'
type: 'feature'
created: '2026-10-08'
status: 'done'
review_loop_iteration: 0
baseline_commit: 'e27910f1e3f760ec87e6773c31310b955b195780'
context:
  - '{project-root}/_bmad-output/implementation-artifacts/spec-5-2-provider-failure-policy.md'
  - '{project-root}/_bmad-output/planning-artifacts/epics/epic-5-operational-safety/stories/5-2-handle-provider-and-background-failures/requirements.md'
  - '{project-root}/_bmad-output/planning-artifacts/epics/epic-5-operational-safety/stories/5-2-handle-provider-and-background-failures/contract.md'
  - '{project-root}/apps/api/AGENTS.md'
---

## Intent

Apply the approved failure taxonomy to the named synchronous `patch-proposal`
operation. Bound attempts to two within a 60-second window, classify provider
outcomes before recording attempt truth, and append one sanitized operational
event per attempt. No queue, worker, production provider, or unvalidated Patch
write is introduced.

## Decisions

- Retry candidates are caller-visible policy results, not an automatic retry.
- Malformed, validation, cancelled, unknown, or unvalidated outcomes never
  authorize trusted state.
- Audit persistence is append-only and failure never falls back to unsafe logs.
- Async lifecycle remains explicitly deferred by E5-DEC-003.

## Tasks and acceptance

- [x] Add deterministic `ProviderRetryPolicy` with fixed count/window bounds.
- [x] Integrate classifier and retry policy into `PatchService` attempt status.
- [x] Emit sanitized audit metadata for successful and failed patch attempts.
- [x] Verify existing Patch generation/regeneration flows remain green.

## Verification

- `php artisan test --compact tests/Feature/Patch/GeneratePatchProposalTest.php tests/Feature/Patch/PatchRegenerationTest.php`
- `php artisan test --compact tests/Feature/OperationalSafety/ProviderFailureClassifierTest.php tests/Feature/OperationalSafety/ProviderRetryPolicyTest.php`
- `vendor/bin/pint --dirty --format agent`
- `git diff --check`

## Suggested Review Order

- Review bounded retry count/window and fail-closed budget behavior.
  [`ProviderRetryPolicy.php:7`](../../apps/api/app/Application/OperationalSafety/ProviderRetryPolicy.php#L7)

- Review classifier integration, attempt status mapping, and audit emission boundary.
  [`PatchService.php:20`](../../apps/api/app/Application/Patch/PatchService.php#L20)

- Verify Patch flow, classifier matrix, retry policy, and sanitized event evidence.
  [`GeneratePatchProposalTest.php:19`](../../apps/api/tests/Feature/Patch/GeneratePatchProposalTest.php#L19)
