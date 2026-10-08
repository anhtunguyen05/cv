---
title: '5.7 Operational safety manifest validator foundation'
type: 'verification'
created: '2026-10-08'
status: 'done'
baseline_commit: 'e27910f1e3f760ec87e6773c31310b955b195780'
review_loop_iteration: 0
context:
  - /home/tuna2/notthing/cv/_bmad-output/planning-artifacts/epics/epic-5-operational-safety/stories/5-7-verify-the-operational-safety-baseline/README.md
  - /home/tuna2/notthing/cv/_bmad-output/planning-artifacts/epics/epic-5-operational-safety/stories/5-7-verify-the-operational-safety-baseline/contract.md
  - /home/tuna2/notthing/cv/_bmad-output/planning-artifacts/epics/epic-5-operational-safety/decisions.md
  - /home/tuna2/notthing/cv/apps/api/AGENTS.md
---

# Story 5.7 — Operational safety manifest validator foundation

This is a bounded implementation slice. It does not close Story 5.7 or the
Epic: the full baseline still requires completion evidence from committed
Stories 5.1–5.6 and approval of `E5-DEC-008` and `E5-DEC-009`.

## Intent

Provide a read-only, fail-closed validator for an immutable readiness manifest.
The validator checks scope, source and artifact hashes, evidence freshness,
decision resolution, canary-safe content, gaps, waivers, and the declared
verdict. It never writes product state and cannot turn missing or failed
evidence into a pass.

## Boundaries

- The default fixture is deliberately `fail` while `E5-DEC-008` remains open.
- Custom manifests are accepted only in the Laravel testing environment and
  only from disposable temporary or approved fixture paths.
- Artifact paths are repository-relative, private, and hash-checked; no raw
  CV/JD/provider payload, credential, prompt, token, cookie, or email field is
  accepted.
- `pass_with_gaps` requires an active, owner-assigned waiver for every gap.
- This slice does not implement operator RBAC, retention/deletion execution,
  provider traffic, queue/worker behavior, production release, or human
  approval.

## Artifacts and code map

- `apps/api/app/Application/OperationalSafety/OperationalSafetyManifestValidator.php`
  — pure read-only manifest validation and stable verdict/exit classes.
- `apps/api/app/Console/Commands/ValidateOperationalSafety.php` —
  `safety:baseline` JSON CLI boundary.
- `docs/contracts/operational-safety/fixtures/baseline-manifest-v1.json` —
  explicit, intentionally failing disposable baseline fixture.
- `apps/api/tests/Feature/OperationalSafety/OperationalSafetyManifestValidatorTest.php`
  — pass, unresolved decision, stale evidence, tamper, canary, waiver, verdict,
  and CLI exit coverage.

## Verification

- `php artisan test --compact tests/Feature/OperationalSafety/OperationalSafetyManifestValidatorTest.php`
  — 10 tests, 32 assertions passed.
- `php artisan test --compact` — 105 tests, 104 passed, 1 skipped, 2,191
  assertions.
- `vendor/bin/pint --dirty --format agent` — passed.
- `php artisan safety:baseline` — expected exit `21` (`fail`) because
  `E5-DEC-008` is unresolved.
- `git diff --check` and PHP lint — passed.

## Remaining Story 5.7 gates

The validator is not a substitute for `TASK-5-7-01` scope/approval fixtures or
`TASK-5-7-03` cross-control evidence and human verdict. Those remain blocked by
the canonical decision register and by the completion evidence of the other
Epic 5 stories.
