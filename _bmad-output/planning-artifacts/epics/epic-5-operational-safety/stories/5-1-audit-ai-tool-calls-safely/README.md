---
story_key: 5-1-audit-ai-tool-calls-safely
title: Audit AI tool calls safely
type: hardening
created: 2026-09-12
story_owner: unassigned
depends_on_stories:
  - 4-3-generate-a-patch-proposal
source_story: _bmad-output/planning-artifacts/epics.md
source_story_key: 5-1-audit-ai-tool-calls-safely
review_loop_iteration: 0
context:
  - _bmad-output/planning-artifacts/epics/epic-5-operational-safety/README.md
  - _bmad-output/planning-artifacts/epics/epic-5-operational-safety/contracts.md
  - _bmad-output/planning-artifacts/epics/epic-5-operational-safety/decisions.md
---

# Story 5.1: Audit AI tool calls safely

**Lifecycle:** Read only from `_bmad-output/implementation-artifacts/sprint-status.yaml`.

**Delivery classification:** Post-MVP hardening.

**Planning blockers:** `E5-PREREQ-AI-001`, `E5-DEC-001`, `E5-DEC-002`,
`E5-DEC-004`, `E5-DEC-008`, `E5-COORD-AUDIT-001`, `E5-COORD-TEST-001`,
and `DISCOVERY-E5-001`.

## Package map

| [Requirements](requirements.md) | [Contract](contract.md) | [Tasks](tasks.md) | [Verification](verification.md) |
| --- | --- | --- | --- |
| Behavior/security/edge cases | Audit/query schema and errors | Atomic work/dependencies | AC/evidence gate |

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

Record useful sanitized provider/tool outcome metadata and let an authorized
operator inspect it as non-authoritative evidence, never as CV/Patch state or a
write path.

</frozen-after-approval>

## Canonical Acceptance Criteria

- **AC-5-1-audit-ai-tool-calls-safely-01:** Given an AI tool call completes or
  fails, record tool, provider, model, status, duration and failure category
  while excluding/redacting credentials and unnecessary raw CV/JD content.
- **AC-5-1-audit-ai-tool-calls-safely-02:** Given an operator views the record,
  distinguish it from trusted product state and prevent it from directly
  modifying a CV Version.

## Readiness coverage

Covers schema/taxonomy/versioning, allowlist redaction, correlation, append-only
write, fail-open/closed, operator RBAC/query/pagination/export, retention,
canaries, injection/oversize, accessibility, and E2E. Code map: `apps/api/app/`,
`database/migrations/`, `routes/api.php`, optional approved operator UI.
