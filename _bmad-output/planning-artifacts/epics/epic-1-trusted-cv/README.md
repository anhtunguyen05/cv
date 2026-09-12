---
epic_key: epic-1
title: Create and Manage a Trusted CV
created: 2026-09-10
source: _bmad-output/planning-artifacts/epics.md
stories:
  - 1-1-register-an-account
  - 1-2-sign-in-and-sign-out
  - 1-3-create-a-cv-profile
  - 1-4-manage-cv-summary-and-skills
  - 1-5-manage-education-and-experience
  - 1-6-manage-projects
  - 1-7-manage-supplementary-cv-sections
  - 1-8-create-and-view-an-immutable-cv-version
---

# Epic 1: Create and Manage a Trusted CV

## Purpose

Epic 1 establishes the trusted User boundary and the structured CV source used
by every later matching, Preview, Export, and AI capability. A User can create
and access an account, maintain a mutable CV Profile, and preserve named,
immutable CV Versions.

This package is the shared planning contract for Stories 1.1 through 1.8. It
does not replace their canonical intent in `epics.md`, approve unresolved
product decisions, or authorize implementation.

## Scope

- Account registration, sign-in, sign-out, current-account retrieval, and
  expiry recovery for the first-party web application.
- Server-enforced ownership and non-disclosing access behavior.
- Structured CV Profile creation and editing across personal information,
  summary, skills, education, experience, projects, certificates, languages,
  and activities.
- Named immutable CV Version creation, listing, and retrieval.
- Cross-layer contracts, validation, security, interaction states, and
  verification needed by all eight stories.

## Out of scope

- Account recovery, social sign-in, MFA, roles, administration, and email
  verification unless an approved decision adds a separate story.
- Job Description, matching, Preview, Export, Patch, AI, worker, queue, or
  server-side PDF behavior.
- Stable production contracts under `docs/contracts/`; this package must pass
  review before any contract is promoted there.

## Source requirements

- `FR-1`: account access.
- `FR-2`: ownership isolation.
- `FR-3`: create and edit structured CV Profiles.
- `FR-4`: create immutable CV Versions.
- `FR-5`: preserve source integrity.
- `NFR-1`: privacy and authorization.
- `NFR-2`: data integrity and traceability.
- `NFR-3`: usability and accessibility.
- `NFR-4`: explicit failure behavior without partial trusted state.

## Global references

- Architecture: `AD-1`, `AD-2`, `AD-3`, `AD-4`, `AD-12` through `AD-19`.
- API and errors: `API-STD-001` through `API-STD-007`,
  `HTTP-CONTRACT-000` through `HTTP-CONTRACT-006`, `ERROR-STD-001` through
  `ERROR-STD-003`.
- Security and validation: `SEC-STD-001` through `SEC-STD-006`,
  `VAL-STD-001` through `VAL-STD-005`.
- Data and reliability: `DATA-STD-001` through `DATA-STD-008`,
  `REL-STD-001` through `REL-STD-005`.
- Accessibility, observability, and testing: `A11Y-STD-001` through
  `A11Y-STD-003`, `OBS-STD-001`, `TEST-STD-001` through `TEST-STD-007`.

## Epic package

| Artifact | Owns |
| --- | --- |
| [Business rules](business-rules.md) | Rules shared by two or more Epic 1 stories |
| [Contracts](contracts.md) | Shared account, Profile, Version, and error agreements |
| [Data and lifecycle](data-and-lifecycle.md) | Ownership, identity, mutability, and state transitions |
| [Security and access](security-and-access.md) | Epic-specific auth, privacy, and abuse controls |
| [UX and validation](ux-and-validation.md) | Shared form, editor, interaction, and validation behavior |
| [Test strategy](test-strategy.md) | Cross-story verification layers and integration gates |
| [Decisions](decisions.md) | Human-owned decisions blocking readiness or implementation |

## Story packages

Each story is one folder under this Epic. `README.md` owns intent and canonical
acceptance criteria; the remaining files separate requirements, contract,
tasks, and verification without creating a second story copy.

| Story | Package |
| --- | --- |
| 1.1 Register an account | [Open](stories/1-1-register-an-account/README.md) |
| 1.2 Sign in and sign out | [Open](stories/1-2-sign-in-and-sign-out/README.md) |
| 1.3 Create a CV Profile | [Open](stories/1-3-create-a-cv-profile/README.md) |
| 1.4 Manage CV summary and skills | [Open](stories/1-4-manage-cv-summary-and-skills/README.md) |
| 1.5 Manage education and experience | [Open](stories/1-5-manage-education-and-experience/README.md) |
| 1.6 Manage projects | [Open](stories/1-6-manage-projects/README.md) |
| 1.7 Manage supplementary CV sections | [Open](stories/1-7-manage-supplementary-cv-sections/README.md) |
| 1.8 Create and view an immutable CV Version | [Open](stories/1-8-create-and-view-an-immutable-cv-version/README.md) |

## Story map and ordering

```text
1.1 Register account ──┐
                      ├── 1.2 Sign in/out ── 1.3 Create CV Profile
                      │                         ├── 1.4 Summary and skills
                      │                         ├── 1.5 Education and experience
                      │                         ├── 1.6 Projects
                      │                         └── 1.7 Supplementary sections
                      │                                      ⇅
                      └──────────────────────────────────────┴── 1.8 CV Version
```

- Story 1.2 reuses the User/session contract established by Story 1.1.
- Story 1.3 establishes the owned mutable Profile aggregate.
- Stories 1.4 through 1.7 may be implemented in parallel after the Profile
  contract and shared editor boundary are approved.
- Story 1.8 may start after Story 1.3 and the complete Profile schema contract
  is frozen. Its persistence foundation may proceed alongside Stories 1.4
  through 1.7; their Version-regression checks and Story 1.8 completion meet at
  the shared Version integration gate.

## Epic Definition of Ready

- Every open row in `decisions.md` has one owner, an approved resolution, and
  dated approval evidence.
- The approved contract subset required by a story is promoted to stable
  `docs/contracts/` sources before that story moves to `ready-for-dev`;
  executable fixtures reference those sources instead of redefining them.
- Shared rule and contract IDs are accepted and referenced by all eight story
  packages without duplication.
- Every story preserves its canonical AC intent and maps every AC to bounded,
  dependency-aware tasks.
- Cross-story ownership, editor, auth, Profile schema, and Version snapshot
  boundaries have explicit coordination records.
- The sprint integration owner has approved readiness order and lifecycle
  synchronization. Story folders never move when lifecycle changes.

## Epic Definition of Done

- All eight canonical stories are `done` with implementation and verification
  evidence; completing an Epic document alone does not satisfy this condition.
- All protected operations enforce authenticated ownership and non-disclosure.
- All Profile sections persist and reload as approved structured data.
- Existing CV Versions remain byte-for-byte equivalent at the contract level
  after later Profile edits.
- Account access and CV Version critical journeys pass Playwright; API,
  authorization, persistence, and migration behavior pass against MySQL 8.4.
- Stable approved contracts have been promoted to the appropriate `docs/`
  source of truth without leaving duplicate competing definitions here.
