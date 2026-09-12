---
story_key: 4-3-generate-a-patch-proposal
title: Generate a Patch proposal
type: feature
created: 2026-09-12
story_owner: unassigned
depends_on_stories:
  - 4-2-answer-evidence-questions
source_story: _bmad-output/planning-artifacts/epics.md
source_story_key: 4-3-generate-a-patch-proposal
review_loop_iteration: 0
context:
  - _bmad-output/planning-artifacts/epics/epic-4-ai-revision/README.md
  - _bmad-output/planning-artifacts/epics/epic-4-ai-revision/contracts.md
  - _bmad-output/planning-artifacts/epics/epic-4-ai-revision/decisions.md
---

# Story 4.3: Generate a Patch proposal

**Lifecycle:** Read only from `_bmad-output/implementation-artifacts/sprint-status.yaml`.

**Planning blockers:** `E4-PREREQ-VERSION-001`, `E4-PREREQ-MATCH-001`,
`E4-DEC-001` through `E4-DEC-006`, `E4-DEC-008`, `E4-DEC-009`,
`E4-COORD-INTERVIEW-001`, `E4-COORD-PROVIDER-001`, `E4-COORD-PATCH-001`,
`E4-COORD-TEST-001`, `DISCOVERY-E4-001`, and `DISCOVERY-E4-002`.

## Package map

| File | Purpose |
| --- | --- |
| [Requirements](requirements.md) | Generation, grounding, provider, validation, persistence, UX, integration, edge cases |
| [Contract](contract.md) | Provider/generate/Patch request, response, status/error, and responsibility boundaries |
| [Tasks](tasks.md) | Atomic tasks with dependency, scope, blocker, and verification metadata |
| [Verification](verification.md) | AC mapping, adversarial/evaluation layers, and exit gate |

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

Use one controlled orchestrator to request an Evidence-grounded structured
proposal, validate it as untrusted input, and persist only a valid pending Patch
while leaving the source CV Version unchanged.

</frozen-after-approval>

## Canonical Acceptance Criteria

- **AC-4-3-generate-a-patch-proposal-01:** Given I own an Interview with
  submitted answers, when I request generation, then a Patch identifies section,
  field, old/new values, reason, and Evidence sources, is stored pending review,
  and leaves the source Version unchanged.
- **AC-4-3-generate-a-patch-proposal-02:** Given provider output has unsupported
  fields, missing Evidence, or malformed data, when generation completes, then
  the proposal is rejected, no unvalidated pending Patch is stored, and failure
  is recorded without provider-secret exposure.

## Readiness coverage

Covers eligible Evidence context, minimum disclosure, prompt/tool/model versions,
injection, provider retry/cancel/late/lost result, strict Patch schema/allowlist,
grounding, sanitized audit, pending persistence transaction, source immutability,
UX states, evaluation thresholds, and critical E2E.

## References

- Epic: `E4-BR-001` through `E4-BR-014`, `E4-CONTRACT-PROVIDER-001`,
  `E4-CONTRACT-PATCH-001`, `E4-CONTRACT-ERROR-001`, `E4-DATA-002`,
  `E4-SEC-001` through `E4-SEC-005`, all provider/Patch/test coordination.
- Code map: `apps/api/app/`, optional controlled integration under API boundary,
  `apps/web/src/`; exact adapter/prompt/fixture paths require coordination.
