---
title: '5.4 Implement the privacy-safe telemetry boundary'
type: 'feature'
created: '2026-10-08'
status: 'done'
review_loop_iteration: 0
baseline_commit: 'e27910f1e3f760ec87e6773c31310b955b195780'
context:
  - '{project-root}/_bmad-output/implementation-artifacts/epic-5-context.md'
  - '{project-root}/_bmad-output/planning-artifacts/epics/epic-5-operational-safety/stories/5-4-monitor-operational-health/requirements.md'
  - '{project-root}/_bmad-output/planning-artifacts/epics/epic-5-operational-safety/stories/5-4-monitor-operational-health/contract.md'
  - '{project-root}/apps/api/AGENTS.md'
---

## Intent

Provide a vendor-neutral metric event boundary with a versioned taxonomy,
allowlisted bounded labels, explicit no-data semantics, and fail-closed
privacy/cardinality validation. The boundary is pure and non-authoritative;
it does not configure a production vendor or alter product truth.

## Boundaries

- Metric names, operations, statuses, failure categories, and environments are
  allowlisted and versioned.
- Labels contain only operation and environment; User/resource/content/provider
  details are rejected.
- `observed`, `no_data`, and `telemetry_unavailable` remain distinct.
- No vendor credentials, dashboards, alerts, or production sink are added.

## Tasks and acceptance

- [x] Implement the pure metric event builder and stable schema.
- [x] Reject unknown fields, sensitive/content labels, invalid values, and
  inconsistent data-state/taxonomy combinations.
- [x] Preserve zero as an observed measurement and represent no-data without a
  fabricated zero.
- [x] Add a checked-in content-free metric contract fixture and focused tests.

## Verification

- `php artisan test --compact tests/Feature/OperationalSafety/PrivacySafeMetricEventBuilderTest.php`
- `vendor/bin/pint --dirty --format agent`
- `php -l app/Application/OperationalSafety/PrivacySafeMetricEventBuilder.php`
- `git diff --check`

## Suggested Review Order

- Review taxonomy, label allowlist, data-state semantics, and bounded values.
  [`PrivacySafeMetricEventBuilder.php:7`](../../apps/api/app/Application/OperationalSafety/PrivacySafeMetricEventBuilder.php#L7)

- Verify content-free contract and versioned schema fixture.
  [`metric-event-v1.json:1`](../../docs/contracts/operational-safety/fixtures/metric-event-v1.json#L1)

- Walk no-data, zero, privacy, taxonomy, and determinism tests.
  [`PrivacySafeMetricEventBuilderTest.php:10`](../../apps/api/tests/Feature/OperationalSafety/PrivacySafeMetricEventBuilderTest.php#L10)
