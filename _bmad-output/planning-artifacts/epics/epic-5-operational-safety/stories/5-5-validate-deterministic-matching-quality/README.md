---
story_key: 5-5-validate-deterministic-matching-quality
title: Validate deterministic matching quality
type: quality
created: 2026-09-12
story_owner: unassigned
depends_on_stories:
  - 2-4-generate-a-match-report
source_story: _bmad-output/planning-artifacts/epics.md
source_story_key: 5-5-validate-deterministic-matching-quality
review_loop_iteration: 0
context:
  - _bmad-output/planning-artifacts/epics/epic-5-operational-safety/README.md
  - _bmad-output/planning-artifacts/epics/epic-5-operational-safety/contracts.md
  - _bmad-output/planning-artifacts/epics/epic-5-operational-safety/decisions.md
---

# Story 5.5: Validate deterministic matching quality

**Lifecycle:** Read only from `_bmad-output/implementation-artifacts/sprint-status.yaml`.

**Delivery classification:** MVP baseline.

**Planning blockers:** `E5-PREREQ-MATCH-001`, `E5-DEC-006`, `E5-DEC-008`,
`E5-COORD-QUALITY-001`, `E5-COORD-TEST-001`, and `DISCOVERY-E2-001`.

## Package map

| [Requirements](requirements.md) | [Contract](contract.md) | [Tasks](tasks.md) | [Verification](verification.md) |
| --- | --- | --- | --- |
| Evaluation/quality/edge cases | Fixture/result/threshold schema | Atomic work/dependencies | AC/evidence gate |

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

Evaluate the deterministic matcher against a versioned synthetic corpus, report
repeatability and rule-version quality, and detect regressions without changing
stored Match Reports or User data.

</frozen-after-approval>

## Canonical Acceptance Criteria

- **AC-5-5-validate-deterministic-matching-quality-01:** Given a deterministic
  Match Report fixture, when validation runs, report repeatability and rule-
  version results without silently changing the stored Match Report.

## Readiness coverage

Covers representative corpus/approval, exact inputs/expected output, engine/rule/
schema/metric/tool versions, repeatability, classification/score/order metrics,
counter-metrics, thresholds, fixture diagnostics, CI/local commands, mutation
guard, performance, artifact retention, change/waiver, and regression triage.
