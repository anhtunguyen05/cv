# Testing Standard

References: AD-17.

- **TEST-STD-001:** Map every story acceptance criterion to one or more verification checks.
- **TEST-STD-002:** Use PHPUnit for PHP domain/application tests and Laravel HTTP feature tests.
- **TEST-STD-003:** Use Vitest for Vue/TypeScript unit, component, and composable tests.
- **TEST-STD-004:** Run migration, persistence, authorization-policy, and API-contract integration
  tests against MySQL 8.4.
- **TEST-STD-005:** Use Playwright for critical browser journeys: authenticated account access,
  CV Version, Job Description analysis, Match Report, Preview, and Export.
- **TEST-STD-006:** Deterministic analysis/matching requires versioned fixtures and repeatability
  tests. A regression requires a test demonstrating the repaired behavior.
- **TEST-STD-007:** E2E database reset/seeding is permitted only when its target is explicitly
  declared disposable. No coverage percentage substitutes for these rules.
