---
epic_key: epic-2
title: Understand CV Fit for a Job
created: 2026-09-12
source: _bmad-output/planning-artifacts/epics.md
stories:
  - 2-1-save-a-job-description
  - 2-2-manage-saved-job-descriptions
  - 2-3-analyze-a-job-description
  - 2-4-generate-a-match-report
  - 2-5-review-an-explainable-match-report
---

# Epic 2: Understand CV Fit for a Job

## Purpose

Epic 2 lets an authenticated User preserve a target Job Description, inspect a
deterministic interpretation of its requirements, compare one immutable CV
Version against it, and understand an explainable Match Report without relying
on an LLM or inventing unsupported claims.

This package is the shared planning contract for Stories 2.1 through 2.5. It
does not replace canonical intent in `epics.md`, approve open decisions, or
authorize implementation.

## Scope

- Owned logical Job Descriptions with immutable revisions and preserved raw
  text.
- Update and logical deletion behavior that preserves historical reproducibility.
- Deterministic Job Description analysis pinned to one exact revision and
  analysis-rule version.
- Deterministic Match Reports pinned to one CV Version, Job Description,
  revision, Analysis, and matching-rule version.
- Explainable matched, missing, and Weak Evidence classifications with safe,
  accessible presentation.
- Cross-layer contracts, validation, security, failure states, and verification
  for all five Stories.

## Out of scope

- File upload, CV/JD parsing, URL import, LinkedIn sync, or external job-board
  integration.
- LLM analysis, AI-generated Match Reports, AI rewriting, or automatic CV
  mutation.
- Full ATS scoring, recruiter ranking, automated applications, or employer
  workflows.
- Queued/worker analysis until measured synchronous behavior justifies it.
- Stable production contracts under `docs/contracts/`; reviewed and approved
  subsets must be promoted there before implementation.

## Source requirements

- `FR-6`: create, read, revise, and logically delete owned Job Descriptions.
- `FR-7`: deterministic analysis with explicit absent/unknown signals.
- `FR-8`: deterministic Match Report from exact immutable sources.
- `FR-9`: explain matched, missing, and Weak Evidence without unsupported claims.
- `NFR-1`: privacy and server-side authorization.
- `NFR-2`: source identity, traceability, and historical reproducibility.
- `NFR-3`: complete interaction states and keyboard accessibility.
- `NFR-4`: explicit failure behavior without partial trusted state.
- `NFR-5`: repeatability for unchanged inputs and rule versions.

## Global and cross-Epic references

- Architecture: `AD-1`, `AD-2`, `AD-4`, `AD-5`, `AD-11`, and `AD-13`
  through `AD-19`.
- API/errors: `API-STD-001` through `API-STD-007`, `HTTP-CONTRACT-000`
  through `HTTP-CONTRACT-006`, and `ERROR-STD-001` through `ERROR-STD-003`.
- Security/data/reliability: `SEC-STD-001` through `SEC-STD-007`,
  `DATA-STD-001` through `DATA-STD-008`, and `REL-STD-001` through
  `REL-STD-005`.
- Accessibility/testing: `A11Y-STD-001` through `A11Y-STD-003` and
  `TEST-STD-001` through `TEST-STD-007`.
- Epic 1 consumers: `E1-CONTRACT-AUTH-001`, `E1-CONTRACT-VERSION-001`,
  `E1-COORD-AUTH-001`, `E1-COORD-VERSION-001`, and `E1-COORD-TEST-001`.

## Epic package

| Artifact | Owns |
| --- | --- |
| [Business rules](business-rules.md) | Rules shared by two or more Epic 2 Stories |
| [Contracts](contracts.md) | Shared Job Description, Analysis, Match Report, and error agreements |
| [Data and lifecycle](data-and-lifecycle.md) | Identity, immutability, revision, analysis, report, and deletion states |
| [Security and access](security-and-access.md) | Ownership, non-disclosure, sensitive content, and abuse controls |
| [UX and validation](ux-and-validation.md) | Shared intake, management, analysis, compare, and report states |
| [Test strategy](test-strategy.md) | Versioned fixtures, repeatability, cross-layer evidence, and gates |
| [Decisions](decisions.md) | Human-owned decisions and coordination records blocking readiness |

## Story packages

Each Story is one permanent folder under this Epic. Lifecycle remains only in
`sprint-status.yaml`.

| Story | Package |
| --- | --- |
| 2.1 Save a Job Description | [Open](stories/2-1-save-a-job-description/README.md) |
| 2.2 Manage saved Job Descriptions | [Open](stories/2-2-manage-saved-job-descriptions/README.md) |
| 2.3 Analyze a Job Description | [Open](stories/2-3-analyze-a-job-description/README.md) |
| 2.4 Generate a Match Report | [Open](stories/2-4-generate-a-match-report/README.md) |
| 2.5 Review an explainable Match Report | [Open](stories/2-5-review-an-explainable-match-report/README.md) |

## Story map and ordering

```text
2.1 Save JD ──┬──> 2.2 Manage/revise/delete JD
              └──> 2.3 Analyze current revision ──┐
Epic 1 CV Version ────────────────────────────────┼──> 2.4 Generate Match Report
                                                 └───────────────> 2.5 Review report
```

- Story 2.1 establishes the owned logical Job Description and initial revision.
- Stories 2.2 and 2.3 may progress in parallel after the revision contract and
  persistence checkpoint are approved.
- Story 2.4 consumes a successful Analysis for the current non-deleted revision
  and an immutable CV Version from Epic 1.
- Story 2.5 consumes the immutable Match Report and must not recompute it in the
  browser.

## Epic Definition of Ready

- Every applicable row in `decisions.md` has one owner, an approved resolution,
  and dated approval evidence.
- Approved shared contracts are promoted to stable `docs/contracts/` sources
  before dependent Stories move to `ready-for-dev`.
- Epic 1 auth and CV Version consumer checkpoints are approved.
- Every canonical AC maps to bounded, dependency-aware tasks and planned
  evidence.
- Revision, analysis, matching, and shared test boundaries have accepted
  coordination records.
- Story lifecycle remains explicit in `sprint-status.yaml`; Story folders never
  move or duplicate when state changes.

## Epic Definition of Done

- All five canonical Stories are `done` with implementation and verification
  evidence.
- Owned raw Job Description text survives reload and every edit creates an
  immutable, identifiable revision.
- Analysis and Match Reports remain reproducible after later edits or logical
  deletion.
- The same source IDs and rule versions produce the same deterministic outputs.
- UI and API never fabricate signals, evidence, or recommendations.
- API, ownership, persistence, migration, deterministic fixture, component,
  accessibility, and critical browser journey checks pass against declared
  environments.
