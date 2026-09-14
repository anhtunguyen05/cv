---
story_key: 5-2-handle-provider-and-background-failures
title: Handle provider and background failures
type: hardening
created: 2026-09-12
story_owner: unassigned
depends_on_stories:
  - 5-1-audit-ai-tool-calls-safely
source_story: _bmad-output/planning-artifacts/epics.md
source_story_key: 5-2-handle-provider-and-background-failures
review_loop_iteration: 0
context:
  - _bmad-output/planning-artifacts/epics/epic-5-operational-safety/README.md
  - _bmad-output/planning-artifacts/epics/epic-5-operational-safety/contracts.md
  - _bmad-output/planning-artifacts/epics/epic-5-operational-safety/decisions.md
---

# Story 5.2: Handle provider and background failures

**Lifecycle:** Read only from `_bmad-output/implementation-artifacts/sprint-status.yaml`.

**Delivery classification:** Post-MVP hardening.

**Planning blockers:** `E5-PREREQ-AI-001`, conditional
`E5-PREREQ-ASYNC-001`, `E5-DEC-002`, `E5-DEC-003`, `E5-DEC-005`,
`E5-DEC-008`, `E5-COORD-AUDIT-001`, `E5-COORD-JOB-001`,
`E5-COORD-TELEMETRY-001`, and `E5-COORD-TEST-001`.

## Package map

| [Requirements](requirements.md) | [Contract](contract.md) | [Tasks](tasks.md) | [Verification](verification.md) |
| --- | --- | --- | --- |
| Failure/lifecycle/edge cases | Provider/job/status errors | Atomic work/dependencies | AC/evidence gate |

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

Classify provider failures and, only for an approved named async operation,
maintain an honest job lifecycle with bounded retry and no unvalidated or
partial trusted result.

</frozen-after-approval>

## Canonical Acceptance Criteria

- **AC-5-2-handle-provider-and-background-failures-01:** Given provider timeout,
  rate limit, or malformed data, apply bounded safe retry, show an actionable
  User state, and create no unvalidated Patch or partial trusted state.
- **AC-5-2-handle-provider-and-background-failures-02:** Given an async Export or
  analysis job fails, its viewed status is documented terminal/retryable and is
  never falsely successful.

## Readiness coverage

Covers taxonomy, timeout/rate/malformed, retry/backoff/budget, idempotency,
cancel/late/lost/duplicate, job leases/state/result write, User/operator status,
audit/metrics, provider/worker crash, dependency outage, accessibility, and E2E.
No generic queue is authorized without the async prerequisite.
