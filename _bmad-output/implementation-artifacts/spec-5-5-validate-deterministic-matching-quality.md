---
title: '5.5 Validate deterministic matching quality'
type: 'feature'
created: '2026-10-08'
status: 'done'
baseline_commit: 'e27910f1e3f760ec87e6773c31310b955b195780'
review_loop_iteration: 0
context:
  - /home/tuna2/notthing/cv/_bmad-output/planning-artifacts/epics/epic-5-operational-safety/README.md
  - /home/tuna2/notthing/cv/_bmad-output/planning-artifacts/epics/epic-5-operational-safety/decisions.md
  - /home/tuna2/notthing/cv/_bmad-output/planning-artifacts/epics/epic-5-operational-safety/stories/5-5-validate-deterministic-matching-quality/README.md
  - /home/tuna2/notthing/cv/apps/api/AGENTS.md
---

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

**Problem:** The existing match corpus is exercised through a database-backed feature test, but there is no standalone, reviewable quality verdict that pins the corpus and rule versions, proves repeatability, or fails safely on regressions. This makes the deterministic matcher difficult to validate without touching product state.

**Approach:** Extract the existing deterministic evaluation into a reusable pure boundary, add an immutable manifest for the approved 40 reviewed and 12 held-out synthetic cases, and implement a read-only evaluator plus Artisan command. The evaluator will canonicalize per-case output, compare classifications/order/score, run the approved repeatability checks, and emit safe diagnostics and distinct failure classes without creating or updating product records.

## Boundaries & Constraints

**Always:** Use only the pinned `docs/contracts/jd/fixtures/match-report-v1.json` corpus and its source SHA; require matching analysis, matcher, report, metric, and tool versions; preserve the Epic 2 matcher behavior; use the E5-DEC-006 thresholds (score delta `0.01`, two independent runs, all classifications/order exact); keep outputs synthetic and content-free; keep the evaluator free of persistence repositories and product writes.

**Ask First:** Do not wire the command into CI artifact retention or claim an environment-level pass until E5-DEC-008 has an approved disposable environment and artifact-access decision. Stop rather than inventing a threshold, baseline rewrite, production data source, or operator approval path.

**Never:** Do not modify expected fixture output during a run, read production User/CV/JD/Match Report data, change matcher rules to satisfy the corpus, persist evaluation results as product state, add provider/queue/retention/operator behavior, or silently turn a failed/waived baseline into pass.

## I/O & Edge-Case Matrix

| Scenario | Input / State | Expected Output / Behavior | Error Handling |
|----------|---------------|-----------------------------|----------------|
| HAPPY_PATH | Pinned manifest and unchanged synthetic corpus | Per-case canonical hashes, aggregate metrics, repeatability pass, safe `PASS` verdict | Non-zero only on infrastructure failure |
| VERSION_MISMATCH | Manifest or matcher version differs from approved contract | No matcher invocation; diagnostic identifies incompatible version | Stable invalid-input exit class |
| QUALITY_REGRESSION | Seeded classification, order, score, or counter-metric difference | Failing case diagnostics and `QUALITY_REGRESSION` verdict; expected files unchanged | Non-zero quality exit |
| NON_REPEATABLE | Same input produces different canonical output across runs | `REPEATABILITY_FAILURE` verdict with run hashes | Non-zero repeatability exit |
| PROHIBITED_SOURCE | Production model/repository or mutation path is requested | Evaluation is rejected before data access or write | Fail closed with safe diagnostic |

</frozen-after-approval>

## Code Map

- `apps/api/app/Application/JobFit/MatchService.php` -- current persistence orchestration and private deterministic evaluation; extract/reuse the pure calculation without changing the HTTP/product contract.
- `apps/api/app/Application/JobFit/JobDescriptionAnalyzer.php` -- vocabulary and analysis rule-version source used by the matcher and fixture compatibility checks.
- `apps/api/tests/Feature/JobFit/MatchQualityCorpusTest.php` -- existing 40+12 corpus expectations and database-backed behavior that must remain green after extraction.
- `docs/contracts/jd/fixtures/match-report-v1.json` -- approved synthetic input and expected classifications/scores; never rewrite during evaluation.
- `apps/api/routes/console.php` and `apps/api/app/Console/Commands/` -- Artisan command registration and CLI convention for a non-HTTP quality gate.
- `.github/workflows/ci-api.yml` -- API quality-gate capture/upload; synthetic artifact access and retention remain provisional pending E5-DEC-008.

## Tasks & Acceptance

**Execution:**
- [x] `docs/contracts/jd/fixtures/match-quality-evaluation-v1.json` -- add the immutable manifest with corpus hash, case inventory, source SHA, rule/schema versions, metrics, and approved thresholds.
- [x] `apps/api/app/Application/JobFit/MatchEvaluator.php` and `apps/api/app/Application/JobFit/MatchService.php` -- expose the deterministic calculation through a pure in-memory boundary while preserving persisted Match Report behavior.
- [x] `apps/api/app/Application/JobFit/MatchQualityEvaluator.php` -- validate the manifest/corpus, invoke the pure matcher, canonicalize output, compute metrics, repeatability, diagnostics, and distinct verdicts without persistence access.
- [x] `apps/api/app/Console/Commands/ValidateMatchQuality.php` and `apps/api/routes/console.php` -- expose deterministic CLI execution and stable exit codes for pass, quality regression, repeatability failure, invalid input, and infrastructure failure.
- [x] `apps/api/tests/Feature/JobFit/MatchQualityEvaluatorTest.php` -- cover golden cases, malformed/version-mismatched input, seeded regressions, repeatability, no-mutation/write-trap, and safe diagnostics; keep `MatchQualityCorpusTest` passing.

**Acceptance Criteria:**
- Given the pinned synthetic manifest, when the command runs twice, then every expected classification and order is exact, scores are within `0.01`, canonical hashes repeat, and no User/Match Report/product record is created or changed.
- Given a changed version, fixture, expected result, ordering, score, unsupported-claim counterexample, or repeated output, when validation runs, then it fails with the correct diagnostic/exit class and does not rewrite the baseline.
- Given a prohibited production or persistence dependency, when evaluation is constructed, then it fails closed before reading or writing that source.

**Scope note:** This slice implements the read-only evaluator foundation, local CLI gate, and provisional CI artifact publication. Artifact access/retention approval, waiver enforcement, and independent environment evidence remain canonical Story gates under `E5-DEC-008`; they are not represented as production-ready here.

## Spec Change Log

- 2026-10-08 — Human continuation instruction authorized the next bounded slice: provisional synthetic CI artifact capture and baseline-policy documentation. The known-bad state avoided is an unreviewable or silently rewritten quality baseline; production-like access, retention approval, and waiver enforcement remain gated.

## Verification

**Commands:**
- `php artisan test --compact tests/Feature/JobFit/MatchQualityEvaluatorTest.php tests/Feature/JobFit/MatchQualityCorpusTest.php` -- expected: evaluator and existing corpus tests pass.
- `vendor/bin/pint --dirty --format agent` -- expected: no PHP formatting violations.
- `git diff --check` -- expected: no whitespace errors.

## Verification Results

- Passed: focused evaluator and existing corpus tests (15 tests, 1,278 assertions), PHP lint, Pint, `php artisan match:quality --json`, and the `validate:match-quality` alias.
- Passed: clean, version-mismatch, malformed-input, classification, ordering, unsupported-claim, repeatability, duration-budget, prohibited-source, no-mutation, and CLI exit-code checks.
- Passed provisionally: CI writes and uploads one synthetic JSON artifact with seven-day retention; final access/retention approval, waiver enforcement, independent environment reruns, and production-like evidence remain blocked by `E5-DEC-008` and canonical Story task gates.

## Suggested Review Order

**Read-only evaluation boundary**

- Start with the evaluator entry point and its fail-closed source/version gates.
  [`MatchQualityEvaluator.php:54`](../../apps/api/app/Application/JobFit/MatchQualityEvaluator.php#L54)

- Inspect the pure matcher extraction shared by product matching and evaluation.
  [`MatchEvaluator.php:14`](../../apps/api/app/Application/JobFit/MatchEvaluator.php#L14)

- Confirm persisted Match Report orchestration remains separate from calculation.
  [`MatchService.php:28`](../../apps/api/app/Application/JobFit/MatchService.php#L28)

**Contract and execution surface**

- Review pinned corpus versions, case inventory, thresholds, and metric contract.
  [`match-quality-evaluation-v1.json:1`](../../docs/contracts/jd/fixtures/match-quality-evaluation-v1.json#L1)

- Verify stable CLI output and exit-code propagation for local quality gates.
  [`ValidateMatchQuality.php:10`](../../apps/api/app/Console/Commands/ValidateMatchQuality.php#L10)

- Check the compatibility alias delegates without changing the verdict.
  [`console.php:10`](../../apps/api/routes/console.php#L10)

- Review the provisional CI capture/upload and its explicit failure propagation.
  [`ci-api.yml:78`](../../.github/workflows/ci-api.yml#L78)

- Check baseline change, rollback, and waiver handling without product writes.
  [`match-quality-baseline-policy.md:27`](../../docs/contracts/jd/match-quality-baseline-policy.md#L27)

**Verification evidence**

- Walk golden, regression, repeatability, mutation, duration, and CLI failure tests.
  [`MatchQualityEvaluatorTest.php:16`](../../apps/api/tests/Feature/JobFit/MatchQualityEvaluatorTest.php#L16)

- Confirm the pre-existing database-backed corpus remains green after extraction.
  [`MatchQualityCorpusTest.php:24`](../../apps/api/tests/Feature/JobFit/MatchQualityCorpusTest.php#L24)
