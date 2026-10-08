---
title: '5.6 Freeze the orchestration measurement plan'
type: 'verification'
created: '2026-10-08'
status: 'done'
baseline_commit: 'e27910f1e3f760ec87e6773c31310b955b195780'
review_loop_iteration: 0
context:
  - /home/tuna2/notthing/cv/_bmad-output/implementation-artifacts/epic-5-context.md
  - /home/tuna2/notthing/cv/_bmad-output/planning-artifacts/epics/epic-5-operational-safety/stories/5-6-decide-whether-multi-agent-orchestration-is-justified/README.md
  - /home/tuna2/notthing/cv/_bmad-output/planning-artifacts/epics/epic-5-operational-safety/stories/5-6-decide-whether-multi-agent-orchestration-is-justified/contract.md
  - /home/tuna2/notthing/cv/apps/api/AGENTS.md
---

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

**Problem:** Story 5.6 requires an unbiased, reproducible workload and metric
baseline before any orchestration decision, but the repository has no validator
for that plan. Running an unpinned or incomplete measurement would create an
unreviewable recommendation and could accidentally authorize multi-agent work.

**Approach:** Add a read-only validator and CLI for a synthetic measurement-plan
contract. It will validate the source pin, workload/privacy boundary, repetitions,
metric set, alternatives, limitation rule, and approval state without executing
provider traffic or adding orchestration components.

## Boundaries & Constraints

**Always:** Use synthetic workloads only; pin source/config/plan versions; require
quality, latency, cost, reliability, and operability metrics; require at least
three reproducible runs; compare single-orchestrator tuning, deterministic split,
bounded queue, and multi-agent alternatives under the same safety boundaries;
return safe diagnostics and stable exit codes; never write product state.

**Ask First:** Do not mark the plan approved, execute a provider or production-like
run, publish an adopt/defer ADR, or choose numeric thresholds beyond the current
decision-register recommendation until `E5-PREREQ-AI-001`, `E5-DEC-007`, and
`E5-DEC-008` have named owners and dated approval evidence.

**Never:** Add agents, routers, tools, permissions, queues, provider credentials,
raw User data, or a runtime/configuration migration; infer a production limitation
from the deterministic matching gate; silently convert a pending plan into an
approved baseline.

## I/O & Edge-Case Matrix

| Scenario | Input / State | Expected Output / Behavior | Error Handling |
|----------|---------------|-----------------------------|----------------|
| VALID_PROPOSED_PLAN | Pinned synthetic plan with pending approval | Safe `blocked` result identifying required gates | Non-zero blocked exit |
| INVALID_PLAN | Missing metric, fewer than three runs, mutable source, or production input | No execution; safe diagnostics | Stable invalid-input exit |
| PROHIBITED_SOURCE | Custom path outside disposable testing/fixture roots | Reject before reading it | Fail closed |
| APPROVED_PLAN | Complete plan with approved gates | Validate only; do not execute measurement or adopt orchestration | Safe pass exit; later runner remains separately scoped |

</frozen-after-approval>

## Code Map

- `apps/api/app/Application/OperationalSafety/OperationalSafetyManifestValidator.php` -- existing read-only evidence validation and safe source-path/hash conventions to reuse.
- `apps/api/app/Application/JobFit/MatchQualityEvaluator.php` -- existing synthetic-only evaluator boundary; plan validation must not invoke it as an orchestration proxy.
- `_bmad-output/planning-artifacts/epics/epic-5-operational-safety/decisions.md` -- pending E5-DEC-007/E5-DEC-008 gates and the only approved starting rule for three reproducible runs.
- `_bmad-output/planning-artifacts/epics/epic-5-operational-safety/stories/5-6-decide-whether-multi-agent-orchestration-is-justified/contract.md` -- required measurement-plan and alternatives fields.

## Tasks & Acceptance

**Execution:**
- [x] `docs/contracts/operational-safety/fixtures/orchestration-measurement-plan-v1.json` -- add a synthetic, explicitly pending plan fixture without invented production thresholds.
- [x] `apps/api/app/Application/OperationalSafety/OrchestrationMeasurementPlanValidator.php` -- validate plan schema, versions, workload/privacy boundary, repetitions, metrics, alternatives, and gate status without I/O side effects.
- [x] `apps/api/app/Console/Commands/ValidateOrchestrationPlan.php` and `apps/api/routes/console.php` -- expose `safety:orchestration-plan` with safe JSON and stable blocked/invalid/pass exits.
- [x] `apps/api/tests/Feature/OperationalSafety/OrchestrationMeasurementPlanValidatorTest.php` -- cover proposed/blocked, approved, malformed, prohibited-source, metric drift, and no-write behavior.

**Acceptance Criteria:**
- Given the default synthetic plan, when validation runs, then it returns `blocked` and lists unresolved AI/orchestration/environment gates without executing a provider or changing product data.
- Given a malformed or unsafe plan, when validation runs, then it rejects the input before execution with a stable diagnostic and non-zero exit.
- Given a complete plan with all gates explicitly approved, when validation runs, then it returns `pass` for plan completeness only and does not add or authorize multi-agent runtime components.

## Design Notes

The default fixture intentionally records the decision-register rule as a
limitation criterion rather than inventing numeric quality/latency/cost limits.
Numeric thresholds and the actual measurement runner are separate work after
the human-owned gates are approved.

## Verification

**Commands:**
- `php artisan test --compact tests/Feature/OperationalSafety/OrchestrationMeasurementPlanValidatorTest.php` -- expected: focused validator tests pass.
- `vendor/bin/pint --dirty --format agent` -- expected: formatting passes.
- `git diff --check` -- expected: no whitespace errors.

## Verification Results

- Passed: focused validator suite (10 tests, 38 assertions), Pint, and
  `git diff --check`.
- Passed: full API suite (115 tests, 114 passed, 1 skipped, 2,229 assertions)
  and PHP lint for the new validator and command.
- Passed: default plan returns `blocked` with `execution: not_started`; the
  approved test plan returns `pass` for validation only; malformed, source-pin,
  and prohibited-path inputs fail before execution.

## Suggested Review Order

**Validation boundary**

- Start at the read-only entry point and source-path gate.
  [`OrchestrationMeasurementPlanValidator.php:98`](../../apps/api/app/Application/OperationalSafety/OrchestrationMeasurementPlanValidator.php#L98)

- Review pinned versions, workload/config hashes, metric definitions, alternatives, and privacy checks.
  [`OrchestrationMeasurementPlanValidator.php:176`](../../apps/api/app/Application/OperationalSafety/OrchestrationMeasurementPlanValidator.php#L176)

- Confirm pending approvals stop before any measurement execution.
  [`OrchestrationMeasurementPlanValidator.php:125`](../../apps/api/app/Application/OperationalSafety/OrchestrationMeasurementPlanValidator.php#L125)

**Contract and CLI**

- Inspect the explicitly pending synthetic plan and its immutable workload inventory.
  [`orchestration-measurement-plan-v1.json:1`](../../docs/contracts/operational-safety/fixtures/orchestration-measurement-plan-v1.json#L1)

  [`orchestration-workloads-v1.json:1`](../../docs/contracts/operational-safety/fixtures/orchestration-workloads-v1.json#L1)

- Verify primary CLI option forwarding and stable exit propagation.
  [`ValidateOrchestrationPlan.php:10`](../../apps/api/app/Console/Commands/ValidateOrchestrationPlan.php#L10)

- Verify the compatibility alias preserves `--plan` and the blocked result.
  [`console.php:14`](../../apps/api/routes/console.php#L14)

**Verification evidence**

- Walk blocked, approved-plan, malformed, source-pin, forbidden-content, and CLI tests.
  [`OrchestrationMeasurementPlanValidatorTest.php:18`](../../apps/api/tests/Feature/OperationalSafety/OrchestrationMeasurementPlanValidatorTest.php#L18)
