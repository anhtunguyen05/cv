---
story_key: 1-5-manage-education-and-experience
title: Manage education and experience
type: feature
created: 2026-09-10
status: draft
story_owner: unassigned
depends_on_stories:
  - 1-3-create-a-cv-profile
source_story: _bmad-output/planning-artifacts/epics.md
source_story_key: 1-5-manage-education-and-experience
review_loop_iteration: 0
context:
  - _bmad-output/planning-artifacts/epics/epic-1-trusted-cv/README.md
  - _bmad-output/planning-artifacts/epics/epic-1-trusted-cv/business-rules.md
  - _bmad-output/planning-artifacts/epics/epic-1-trusted-cv/contracts.md
  - _bmad-output/planning-artifacts/epics/epic-1-trusted-cv/decisions.md
---

# Story 1.5: Manage education and experience

**Readiness:** Draft; blocked by `E1-DEC-003` through `E1-DEC-005`,
`E1-DEC-007`, `E1-DEC-008`, and `E1-COORD-PROFILE-001`.

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

**Problem:** A User cannot maintain education and work history as identifiable
structured entries for later comparison.

**Approach:** Add approved education/experience item schemas and atomic
add/edit/remove behavior to the owned mutable Profile.

## References

- Global: `AD-1` through `AD-3`, `AD-14` through `AD-19`,
  `API-STD-001` through `API-STD-007`, `SEC-STD-003` through `SEC-STD-006`,
  `DATA-STD-001` through `DATA-STD-008`, `VAL-STD-001` through
  `VAL-STD-005`, `A11Y-STD-001` through `A11Y-STD-003`, and
  `TEST-STD-001` through `TEST-STD-005`.
- Epic: `E1-BR-001` through `E1-BR-011`, `E1-BR-013`, `E1-BR-014`,
  `E1-CONTRACT-PROFILE-001`, `E1-STATE-PROFILE-001`, `E1-DATA-002` through
  `E1-DATA-007`, `E1-COORD-PROFILE-001`, `E1-COORD-TEST-001`.

## Boundaries & Constraints

**Always:** Use stable server item ULIDs; validate dates/types/limits; scope item
mutation through the owned Profile; preserve unrelated entries and Versions.

**Ask First:** Freeze item fields, date/open-ended semantics, ordering,
collection limits, add/edit/remove payload and stale conflict behavior.

**Never:** Address entries by array index, attach another Profile's item,
persist malformed partial entries, or mutate a saved Version.

## Story-specific rules and edge cases

- **HISTORY-BR-001:** Education and experience remain distinguishable section
  types even when they share reusable date/location fields.
- **HISTORY-BR-002:** Removal targets one stable item ID and does not imply
  deletion of omitted sibling items.

| Scenario | Expected behavior |
| --- | --- |
| Valid add/edit | Stable item ID and reloadable structured values |
| Invalid fields/date range | Item-level errors; no malformed persistence |
| Remove one item | Only target leaves current Profile |
| Foreign/stale item ID | Non-disclosing or conflict outcome; no change |
| Existing Version | Original history remains unchanged |

</frozen-after-approval>

## Canonical Acceptance Criteria

- **AC-1-5-manage-education-and-experience-01:** Given I own a CV Profile, when
  I add valid education/experience entries, then each is stored in its section
  with a stable item ID and can be edited or removed.
- **AC-1-5-manage-education-and-experience-02:** Given an entry missing required
  fields or having invalid types, when I save, then it is rejected and no
  malformed entry is persisted.
- **AC-1-5-manage-education-and-experience-03:** Given I remove an entry, when I
  save, then it leaves the current Profile and existing Versions remain unchanged.

## Readiness coverage

Add/edit/remove, malformed, stale/foreign, reload and Version-regression paths
are explicit. Shared Profile contract, aggregate transaction, ownership,
nested validation, accessible repeated editor, mapping and all test layers are
referenced; field/date semantics remain human-gated.

## Code Map

- Shared Profile aggregate/persistence/API and policy boundaries.
- `apps/web/src/features/cv-profiles/` history schema, API and repeated editor.
- Backend, component and browser verification locations.

## Tasks & Acceptance

**Execution:**

- [ ] TASK-1-5-01: Extend Profile fixtures with history item schemas
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `none`
  - Covers: `AC-1-5-manage-education-and-experience-01` through `AC-1-5-manage-education-and-experience-03`
  - Scope: education/experience fixtures
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: `E1-DEC-003` through `E1-DEC-005`
  - Outcome: Extend Profile fixtures with history item schemas.
  - Acceptance: fields, date rules, IDs, paths, limits and errors are frozen.
  - Verification: schema/AC review.
- [ ] TASK-1-5-02: Implement history item domain and persistence behavior
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-5-01`
  - Covers: `AC-1-5-manage-education-and-experience-01` through `AC-1-5-manage-education-and-experience-03`
  - Scope: Profile aggregate history rules/repository mapping
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: approved `E1-COORD-PROFILE-001` Profile schema and persistence checkpoint from Story 1.3
  - Outcome: Implement history item domain and persistence behavior.
  - Acceptance: add/edit/remove is atomic with stable server IDs.
  - Verification: unit and MySQL persistence/Version-regression tests.
- [ ] TASK-1-5-03: Expose history update contract
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-5-02`
  - Covers: `AC-1-5-manage-education-and-experience-01` through `AC-1-5-manage-education-and-experience-03`
  - Scope: request/resource/use case/errors
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: `none`
  - Outcome: Expose history update contract.
  - Acceptance: nested errors and targeted removal match fixtures without partial writes.
  - Verification: Laravel feature/contract tests.
- [ ] TASK-1-5-04: Build education/experience repeated editor
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-5-01`
  - Covers: `AC-1-5-manage-education-and-experience-01` through `AC-1-5-manage-education-and-experience-03`
  - Scope: frontend schema/API/components/state
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: `E1-DEC-007`
  - Outcome: Build education/experience repeated editor.
  - Acceptance: keyboard-usable stable rows preserve valid siblings and errors.
  - Verification: type-check and Vitest after enablement.
- [ ] TASK-1-5-05: Verify history backend behavior
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-5-03`
  - Covers: `AC-1-5-manage-education-and-experience-01` through `AC-1-5-manage-education-and-experience-03`
  - Scope: unit/feature/MySQL suite
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: `E1-COORD-VERSION-001` persistence fixture checkpoint
  - Outcome: Verify history backend behavior.
  - Acceptance: IDs, atomicity, ownership, validation/removal and immutability pass.
  - Verification: focused PHPUnit suites.
- [ ] TASK-1-5-06: Verify history end to end
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-5-04`, `TASK-1-5-05`
  - Covers: `AC-1-5-manage-education-and-experience-01` through `AC-1-5-manage-education-and-experience-03`
  - Scope: browser add/edit/remove/reload path
  - Coordination: `E1-COORD-TEST-001`
  - Blocked by: `E1-DEC-008`
  - Outcome: Verify history end to end.
  - Acceptance: critical and meaningful failure paths pass on disposable data.
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

- 2026-09-10: Initial Epic-wide draft created from canonical Story 1.5.
