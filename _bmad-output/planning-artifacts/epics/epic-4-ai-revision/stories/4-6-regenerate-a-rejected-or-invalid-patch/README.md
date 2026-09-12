---
story_key: 4-6-regenerate-a-rejected-or-invalid-patch
title: Regenerate a rejected or invalid Patch
type: feature
created: 2026-09-12
story_owner: unassigned
depends_on_stories:
  - 4-3-generate-a-patch-proposal
  - 4-4-review-edit-or-reject-a-patch
source_story: _bmad-output/planning-artifacts/epics.md
source_story_key: 4-6-regenerate-a-rejected-or-invalid-patch
review_loop_iteration: 0
context:
  - _bmad-output/planning-artifacts/epics/epic-4-ai-revision/README.md
  - _bmad-output/planning-artifacts/epics/epic-4-ai-revision/contracts.md
  - _bmad-output/planning-artifacts/epics/epic-4-ai-revision/decisions.md
---

# Story 4.6: Regenerate a rejected or invalid Patch

**Lifecycle:** Read only from `_bmad-output/implementation-artifacts/sprint-status.yaml`.

**Planning blockers:** `E4-PREREQ-VERSION-001`, `E4-PREREQ-MATCH-001`,
`E4-DEC-001` through `E4-DEC-009`, `E4-COORD-INTERVIEW-001`,
`E4-COORD-PROVIDER-001`, `E4-COORD-PATCH-001`, `E4-COORD-TEST-001`,
`DISCOVERY-E4-001`, and `DISCOVERY-E4-002`.

## Package map

| File | Purpose |
| --- | --- |
| [Requirements](requirements.md) | Regeneration eligibility, lineage, provider, validation, UX, integration, edge cases |
| [Contract](contract.md) | Regenerate request/response/status/error and responsibility boundaries |
| [Tasks](tasks.md) | Atomic tasks with dependency, scope, blocker, and verification metadata |
| [Verification](verification.md) | AC mapping, lineage/stale/evaluation evidence, and exit gate |

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

Let the User request a corrected proposal only from an eligible rejected or
invalid predecessor whose exact Evidence/source context remains valid. Preserve
the prior decision and create a separately identifiable lineage result.

</frozen-after-approval>

## Canonical Acceptance Criteria

- **AC-4-6-regenerate-a-rejected-or-invalid-patch-01:** Given I own a rejected
  or invalid Patch and Evidence context remains available, when I regenerate,
  then a new pending proposal is created or unavailability is explained, the
  previous Patch keeps its historical state, and the source Version is unchanged.
- **AC-4-6-regenerate-a-rejected-or-invalid-patch-02:** Given Evidence context is
  no longer valid for the source Version, when I regenerate, then stale context
  is refused and the next required action is explained.

## Readiness coverage

Covers allowed predecessor states, source/Evidence/question/context validity,
lineage, rejection/invalid feedback input, provider contract reuse, minimum
disclosure, dedupe/concurrency/late/lost result, quality validation, immutable
predecessor/source, stale recovery, ownership, UX/accessibility, MySQL, and E2E.

## References

- Epic: `E4-BR-001` through `E4-BR-022`, `E4-CONTRACT-INTERVIEW-001`,
  `E4-CONTRACT-PROVIDER-001`, `E4-CONTRACT-PATCH-001`,
  `E4-CONTRACT-ERROR-001`, `E4-DATA-002`, all security and coordination rules.
- Code map: `apps/api/app/`, `routes/api.php`, `apps/web/src/`; provider, Patch,
  lineage, and fixture paths remain under named coordination owners.
