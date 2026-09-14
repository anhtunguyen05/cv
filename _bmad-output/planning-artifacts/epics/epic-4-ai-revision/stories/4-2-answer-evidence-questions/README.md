---
story_key: 4-2-answer-evidence-questions
title: Answer Evidence questions
type: feature
created: 2026-09-12
story_owner: unassigned
depends_on_stories:
  - 4-1-start-an-evidence-interview
source_story: _bmad-output/planning-artifacts/epics.md
source_story_key: 4-2-answer-evidence-questions
review_loop_iteration: 0
context:
  - _bmad-output/planning-artifacts/epics/epic-4-ai-revision/README.md
  - _bmad-output/planning-artifacts/epics/epic-4-ai-revision/contracts.md
  - _bmad-output/planning-artifacts/epics/epic-4-ai-revision/decisions.md
---

# Story 4.2: Answer Evidence questions

**Lifecycle:** Read only from `_bmad-output/implementation-artifacts/sprint-status.yaml`.

**Planning blockers:** `E4-DEC-001` through `E4-DEC-003`, `E4-DEC-009`,
`E4-COORD-INTERVIEW-001`, and `E4-COORD-TEST-001`.

## Package map

| File | Purpose |
| --- | --- |
| [Requirements](requirements.md) | Answer behavior, provenance, validation, security, UX, integration, edge cases |
| [Contract](contract.md) | Answer request/response/status/error and FE/API responsibilities |
| [Tasks](tasks.md) | Atomic tasks with dependency, scope, blocker, and evidence metadata |
| [Verification](verification.md) | AC mapping and evidence gate |

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

Preserve valid User-authored Evidence for the exact active question, or preserve
an explicit non-supporting outcome, without confusing either with system or AI
content.

</frozen-after-approval>

## Canonical Acceptance Criteria

- **AC-4-2-answer-evidence-questions-01:** Given I own an active Interview, when
  I submit a valid current answer, then it is stored with session and timestamp
  and remains distinguishable from system-generated content.
- **AC-4-2-answer-evidence-questions-02:** Given an empty or over-limit answer,
  when submitted, then an actionable validation error is returned and no empty
  Evidence is stored.
- **AC-4-2-answer-evidence-questions-03:** Given I cannot provide Evidence, when
  I indicate unknown/not performed, then the outcome is recorded and is not
  treated as supporting Evidence.

## Readiness coverage

Covers raw/provenance storage, normalization, explicit negative outcome,
question ordering/version, stale/duplicate/lost/concurrent answers, correction,
validation, safe rendering/logging, accessible question progress, API/FE mapping,
MySQL, and E2E. Exact correction and session progression remain decisions.

## References

- Epic: `E4-BR-001` through `E4-BR-010`, `E4-CONTRACT-EVIDENCE-001`,
  `E4-CONTRACT-ERROR-001`, `E4-DATA-001`, `E4-SEC-001` through `E4-SEC-005`,
  `E4-COORD-INTERVIEW-001`, `E4-COORD-TEST-001`.
- Code map: `apps/api/app/`, `routes/api.php`, `apps/web/src/`; shared fixtures
  and schemas are assigned at coordination checkpoint.
