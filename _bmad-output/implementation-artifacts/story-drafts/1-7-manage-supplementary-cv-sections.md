---
story_key: 1-7-manage-supplementary-cv-sections
title: Manage supplementary CV sections
type: feature
created: 2026-09-10
status: draft
story_owner: unassigned
depends_on_stories:
  - 1-3-create-a-cv-profile
source_story: _bmad-output/planning-artifacts/epics.md
source_story_key: 1-7-manage-supplementary-cv-sections
review_loop_iteration: 0
context:
  - _bmad-output/planning-artifacts/epics/epic-1-trusted-cv/README.md
  - _bmad-output/planning-artifacts/epics/epic-1-trusted-cv/business-rules.md
  - _bmad-output/planning-artifacts/epics/epic-1-trusted-cv/contracts.md
  - _bmad-output/planning-artifacts/epics/epic-1-trusted-cv/decisions.md
---

# Story 1.7: Manage supplementary CV sections

**Readiness:** Draft; blocked by `E1-DEC-003` through `E1-DEC-005`,
`E1-DEC-007`, `E1-DEC-008`, and `E1-COORD-PROFILE-001`.

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

**Problem:** A User cannot represent certificates, languages, and activities
beyond core experience.

**Approach:** Add three optional, structured, independently editable section
types to the shared mutable Profile contract and editor.

## References

- Global: `AD-1` through `AD-3`, `AD-14` through `AD-19`,
  `API-STD-001` through `API-STD-007`, `SEC-STD-003` through `SEC-STD-006`,
  `DATA-STD-001` through `DATA-STD-008`, `VAL-STD-001` through
  `VAL-STD-005`, `A11Y-STD-001` through `A11Y-STD-003`, and
  `TEST-STD-001` through `TEST-STD-005`.
- Epic: `E1-BR-001` through `E1-BR-014`, `E1-CONTRACT-PROFILE-001`,
  `E1-STATE-PROFILE-001`, `E1-DATA-002` through `E1-DATA-007`,
  `E1-COORD-PROFILE-001`, `E1-COORD-TEST-001`.

## Boundaries & Constraints

**Always:** Keep all three sections optional; use stable item IDs; reject
invalid entries without deleting valid siblings; preserve ownership, aggregate
atomicity and Versions.

**Ask First:** Freeze each section's fields, limits, ordering, duplicates,
language proficiency vocabulary, certificate/date/URL semantics and removal.

**Never:** Make optional sections prerequisites for Version/Preview, coerce an
invalid entry into an empty one, attach foreign item IDs, or mutate Versions.

## Story-specific rules and edge cases

- **SUPP-BR-001:** Certificates, languages, and activities remain distinct
  section types and may all be empty.
- **SUPP-BR-002:** An invalid submitted entry cannot silently delete or replace
  previously valid entries.

| Scenario | Expected behavior |
| --- | --- |
| Valid entries | Stored/reloaded in corresponding section with stable IDs |
| One invalid entry | Field error; existing valid entries remain unchanged |
| All sections empty | Profile stays valid and versionable |
| Foreign/stale item ID | Non-disclosing/conflict outcome; no mutation |
| Existing Version | Original supplementary data remains unchanged |

</frozen-after-approval>

## Canonical Acceptance Criteria

- **AC-1-7-manage-supplementary-cv-sections-01:** Given I own a Profile, when I
  add valid certificates, languages, or activities, then each is stored in its
  section and can be edited or removed from the mutable Profile.
- **AC-1-7-manage-supplementary-cv-sections-02:** Given one empty/invalid-type
  entry, when I save, then it receives a field error and valid existing entries
  are not silently deleted.
- **AC-1-7-manage-supplementary-cv-sections-03:** Given all supplementary
  sections are empty, when I save/view, then the Profile remains valid and the
  empty sections do not block Version creation or Preview.

## Readiness coverage

Add/edit/remove, invalid, all-empty, foreign/stale, reload and Version-regression
behavior are explicit. Shared Profile contract, atomicity, ownership,
validation, accessible repeated editors, mapping and all verification layers
are referenced; section schemas remain human-gated.

## Code Map

- Shared Profile aggregate/persistence/API/policy boundaries.
- `apps/web/src/features/cv-profiles/` supplementary schemas and editors.
- Backend, component and browser verification locations.

## Tasks & Acceptance

**Execution:**

- [ ] TASK-1-7-01: Extend Profile fixtures with supplementary schemas
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `none`
  - Covers: `AC-1-7-manage-supplementary-cv-sections-01` through `AC-1-7-manage-supplementary-cv-sections-03`
  - Scope: certificate/language/activity fixtures
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: `E1-DEC-003` through `E1-DEC-005`
  - Outcome: Extend Profile fixtures with supplementary schemas.
  - Acceptance: fields, IDs, optionality, paths, limits and errors are frozen.
  - Verification: schema/AC review.
- [ ] TASK-1-7-02: Implement supplementary domain and persistence behavior
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-7-01`
  - Covers: `AC-1-7-manage-supplementary-cv-sections-01` through `AC-1-7-manage-supplementary-cv-sections-03`
  - Scope: Profile aggregate section rules/repository mapping
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: approved `E1-COORD-PROFILE-001` Profile schema and persistence checkpoint from Story 1.3
  - Outcome: Implement supplementary domain and persistence behavior.
  - Acceptance: optional add/edit/remove is atomic with stable IDs.
  - Verification: unit and MySQL persistence/Version-regression tests.
- [ ] TASK-1-7-03: Expose supplementary update contract
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-7-02`
  - Covers: `AC-1-7-manage-supplementary-cv-sections-01` through `AC-1-7-manage-supplementary-cv-sections-03`
  - Scope: request/resource/use case/errors
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: `none`
  - Outcome: Expose supplementary update contract.
  - Acceptance: optional/invalid/removal behavior matches fixtures without data loss.
  - Verification: Laravel feature/contract tests.
- [ ] TASK-1-7-04: Build supplementary repeated editors
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-7-01`
  - Covers: `AC-1-7-manage-supplementary-cv-sections-01` through `AC-1-7-manage-supplementary-cv-sections-03`
  - Scope: frontend schemas/API/components/state
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: `E1-DEC-007`
  - Outcome: Build supplementary repeated editors.
  - Acceptance: accessible optional editors preserve valid entries and errors.
  - Verification: type-check and Vitest after enablement.
- [ ] TASK-1-7-05: Verify supplementary backend behavior
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-7-03`
  - Covers: `AC-1-7-manage-supplementary-cv-sections-01` through `AC-1-7-manage-supplementary-cv-sections-03`
  - Scope: unit/feature/MySQL suite
  - Coordination: `E1-COORD-PROFILE-001`
  - Blocked by: `E1-COORD-VERSION-001` persistence fixture checkpoint
  - Outcome: Verify supplementary backend behavior.
  - Acceptance: optionality, data preservation, ownership and immutability pass.
  - Verification: focused PHPUnit suites.
- [ ] TASK-1-7-06: Verify supplementary sections end to end
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-1-7-04`, `TASK-1-7-05`
  - Covers: `AC-1-7-manage-supplementary-cv-sections-01` through `AC-1-7-manage-supplementary-cv-sections-03`
  - Scope: browser populated/invalid/all-empty/reload path
  - Coordination: `E1-COORD-TEST-001`
  - Blocked by: `E1-DEC-008`
  - Outcome: Verify supplementary sections end to end.
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

- 2026-09-10: Initial Epic-wide draft created from canonical Story 1.7.
