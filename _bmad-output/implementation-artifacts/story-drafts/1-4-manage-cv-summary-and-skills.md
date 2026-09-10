---
story_key: 1-4-manage-cv-summary-and-skills
title: Manage CV summary and skills
type: feature
created: 2026-09-10
status: draft
story_owner: unassigned
depends_on_stories:
  - 1-3-create-a-cv-profile
source_story: _bmad-output/planning-artifacts/epics.md
source_story_key: 1-4-manage-cv-summary-and-skills
review_loop_iteration: 0
context:
  - _bmad-output/planning-artifacts/epics/epic-1-trusted-cv/README.md
  - _bmad-output/planning-artifacts/epics/epic-1-trusted-cv/business-rules.md
  - _bmad-output/planning-artifacts/epics/epic-1-trusted-cv/contracts.md
  - _bmad-output/planning-artifacts/epics/epic-1-trusted-cv/decisions.md
---

# Story 1.4: Manage CV summary and skills

**Readiness:** Draft; blocked by `E1-DEC-003` through `E1-DEC-005`,
`E1-DEC-007`, `E1-DEC-008`, and `E1-COORD-PROFILE-001`.

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

**Problem:** A User cannot represent their core qualifications as structured
summary and categorized skills on the mutable Profile.

**Approach:** Extend the shared Profile contract/editor with validated summary
and skill-category fields while preserving existing immutable Versions.

## References

- Global: `AD-1` through `AD-3`, `AD-14` through `AD-19`,
  `API-STD-001` through `API-STD-007`, `SEC-STD-003` through `SEC-STD-006`,
  `DATA-STD-001` through `DATA-STD-008`, `VAL-STD-001` through
  `VAL-STD-005`, `A11Y-STD-001` through `A11Y-STD-003`, and
  `TEST-STD-001` through `TEST-STD-005`.
- Epic: `E1-BR-001` through `E1-BR-011`, `E1-BR-013`, `E1-BR-014`,
  `E1-CONTRACT-PROFILE-001`, `E1-CONTRACT-ERROR-001`,
  `E1-STATE-PROFILE-001`, `E1-COORD-PROFILE-001`, `E1-COORD-TEST-001`.

## Boundaries & Constraints

**Always:** Save structured fields atomically under authenticated ownership;
support approved empty categories; preserve unaffected fields and all Versions;
map nested field errors to their controls.

**Ask First:** Freeze summary and category schema, category/item limits,
duplicate/case/ordering policy, save granularity and conflict behavior.

**Never:** Treat skills as one opaque blob, mutate a Version, infer unsupported
skills, or interpret omitted fields as silent deletion.

## Story-specific rules and edge cases

- **SUMMARY-BR-001:** Empty summary is allowed only if the approved Profile
  schema says it is optional; whitespace-only and over-limit states are explicit.
- **SKILL-BR-001:** Each skill belongs to one approved category representation;
  empty/invalid entries are rejected rather than silently coerced.

| Scenario | Expected behavior |
| --- | --- |
| Valid summary/categories | Structured values save and reload |
| Optional empty category | Save succeeds under approved schema |
| Invalid/over-limit skill | Field error; previous aggregate remains unchanged |
| Stale or ambiguous update | Preserve edits and use approved reconciliation |
| Existing Version | Remains unchanged after Profile update |

</frozen-after-approval>

## Canonical Acceptance Criteria

- **AC-1-4-manage-cv-summary-and-skills-01:** Given I own a CV Profile, when I
  save a valid summary and categorized skills, then structured values persist
  and reload, and optional empty categories do not prevent saving.
- **AC-1-4-manage-cv-summary-and-skills-02:** Given an empty, over-limit, or
  invalid-type skill entry, when I save, then it receives a field error and is
  not silently normalized into an unsupported value.
- **AC-1-4-manage-cv-summary-and-skills-03:** Given I update summary or skills,
  when I save, then the mutable Profile changes and every saved Version remains
  unchanged.

## Readiness coverage

Save/reload, optional empty, invalid, stale, auth and Version-regression paths
are covered through shared Profile contracts/rules. Backend aggregate update,
authorization/transaction, validation limits, accessible editor states,
frontend/API mapping and unit/feature/MySQL/component/E2E evidence are planned.

## Code Map

- Shared Profile domain/persistence/API resource and Form Request boundaries.
- `apps/web/src/features/cv-profiles/` summary/skills schema, adapter and editor.
- Backend, component and browser test locations.

## Tasks & Acceptance

**Execution:**

- [ ] TASK-1-4-01: Extend Profile fixtures with summary and skills
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `none`
  - Covers: `AC-1-4-manage-cv-summary-and-skills-01` through `AC-1-4-manage-cv-summary-and-skills-03`
  - Scope: shared Profile schema/fixtures
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: `E1-DEC-003` through `E1-DEC-005`
  - Outcome: Extend Profile fixtures with summary and skills.
  - Acceptance: fields, categories, limits, ordering and errors are frozen once.
  - Verification: fixture schema and AC review.
- [ ] TASK-1-4-02: Implement summary/skills domain and persistence update
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-4-01`
  - Covers: `AC-1-4-manage-cv-summary-and-skills-01` through `AC-1-4-manage-cv-summary-and-skills-03`
  - Scope: Profile aggregate section rules/repository mapping
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: approved `E1-COORD-PROFILE-001` Profile schema and persistence checkpoint from Story 1.3
  - Outcome: Implement summary/skills domain and persistence update.
  - Acceptance: approved structured values update atomically with stable ownership.
  - Verification: PHPUnit plus MySQL persistence/Version-regression tests.
- [ ] TASK-1-4-03: Expose summary/skills update contract
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-4-02`
  - Covers: `AC-1-4-manage-cv-summary-and-skills-01` through `AC-1-4-manage-cv-summary-and-skills-03`
  - Scope: Profile request/resource/update use case/error mapping
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: `none`
  - Outcome: Expose summary/skills update contract.
  - Acceptance: fixtures pass without partial writes or Version mutation.
  - Verification: Laravel feature/contract tests.
- [ ] TASK-1-4-04: Build summary/skills editor integration
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-4-01`
  - Covers: `AC-1-4-manage-cv-summary-and-skills-01` through `AC-1-4-manage-cv-summary-and-skills-03`
  - Scope: feature schema/API/editor/error state
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: `E1-DEC-007`
  - Outcome: Build summary/skills editor integration.
  - Acceptance: accessible add/edit/remove/save states preserve valid data and conflicts.
  - Verification: type-check and Vitest after enablement.
- [ ] TASK-1-4-05: Verify summary/skills backend behavior
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-4-03`
  - Covers: `AC-1-4-manage-cv-summary-and-skills-01` through `AC-1-4-manage-cv-summary-and-skills-03`
  - Scope: unit/feature/MySQL suite
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: `E1-COORD-VERSION-001` persistence fixture checkpoint
  - Outcome: Verify summary/skills backend behavior.
  - Acceptance: valid, empty, invalid, stale and Version-regression evidence passes.
  - Verification: focused PHPUnit suites.
- [ ] TASK-1-4-06: Verify summary/skills end to end
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-4-04`, `TASK-1-4-05`
  - Covers: `AC-1-4-manage-cv-summary-and-skills-01` through `AC-1-4-manage-cv-summary-and-skills-03`
  - Scope: browser save/reload/error/Version-regression path
  - Coordination: `E1-COORD-TEST-001`
  - Blocked by: `E1-DEC-008`
  - Outcome: Verify summary/skills end to end.
  - Acceptance: critical and failure paths pass on disposable data.
  - Verification: approved Playwright command.

## Dependency and concurrency map

```text
01 -> {02,04}; 02 -> 03 -> 05; {04,05} -> 06
```

## Coordination and verification gate

Reserve shared Profile files through `E1-COORD-PROFILE-001`; task 05 waits for
the `E1-COORD-VERSION-001` persistence fixture checkpoint; task 06 uses
`E1-COORD-TEST-001` and gates all three ACs.

## Spec Change Log

- 2026-09-10: Initial Epic-wide draft created from canonical Story 1.4.
