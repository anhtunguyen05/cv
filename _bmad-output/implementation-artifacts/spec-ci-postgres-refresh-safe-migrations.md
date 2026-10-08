---
title: 'Fix PostgreSQL refresh-safe audit migration in CI'
type: 'bugfix'
created: '2026-10-08'
status: 'done'
review_loop_iteration: 0
context: []
route: 'one-shot'
---

# Fix PostgreSQL refresh-safe audit migration in CI

## Intent

**Problem:** The CI job creates the PostgreSQL schema before PHPUnit, then Laravel's `RefreshDatabase` runs `migrate:fresh`. PostgreSQL drops the audit table but retains the standalone append-only trigger function, so the next migration run fails with `Duplicate function`, causing the remaining tests to report cascading errors.

**Approach:** Make the PostgreSQL trigger function definition replacement-safe and add an explicit CI refresh check immediately after the initial migration. This preserves the append-only trigger while covering the exact `migrate` → `migrate:fresh` sequence that failed.

## Suggested Review Order

1. [`apps/api/database/migrations/2026_10_08_000018_create_operational_audit_events_table.php`](../../apps/api/database/migrations/2026_10_08_000018_create_operational_audit_events_table.php) — verify the PostgreSQL function is replacement-safe and trigger behavior is unchanged.
2. [`.github/workflows/ci-api.yml`](../../.github/workflows/ci-api.yml) — verify the refresh regression check runs against the disposable PostgreSQL service before PHPUnit.
3. Run `php artisan test --compact`, `vendor/bin/pint --dirty --format agent`, and `git diff --check`; validate the PostgreSQL job in CI because this environment has no `pdo_pgsql` extension.
