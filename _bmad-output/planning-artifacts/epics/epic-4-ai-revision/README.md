---
epic_key: epic-4
title: Improve a CV with Evidence-Based AI Revision
created: 2026-09-12
source: _bmad-output/planning-artifacts/epics.md
stories:
  - 4-1-start-an-evidence-interview
  - 4-2-answer-evidence-questions
  - 4-3-generate-a-patch-proposal
  - 4-4-review-edit-or-reject-a-patch
  - 4-5-approve-a-patch-into-a-new-cv-version
  - 4-6-regenerate-a-rejected-or-invalid-patch
---

# Epic 4: Improve a CV with Evidence-Based AI Revision

## Purpose

Epic 4 lets an authenticated User respond to targeted gaps from an owned Match
Report, receive a structured AI Patch proposal grounded only in explicit User
Evidence, review or edit it, and explicitly approve a validated change into a
new immutable CV Version.

This post-MVP package defines planning boundaries only. It does not select a
provider/model, approve data disclosure, establish production prompts, or
authorize implementation.

## Scope

- Evidence interview pinned to exact Match Report, CV Version, JD revision, and
  unresolved improvement areas.
- User-authored answers and explicit cannot-provide outcomes with provenance.
- One controlled application orchestrator and strict provider DTO boundary.
- Structured Patch proposal with allowlisted operations and Evidence citations.
- Human review, bounded edit, rejection, revalidation, approval, and regeneration.
- Atomic new CV Version creation without modifying the source Version.
- Fail-closed security, prompt-injection resistance, quality evaluation, audit
  references, retry/idempotency, and ownership isolation.

## Out of scope

- Automatic CV mutation, autonomous application submission, or invented Evidence.
- Provider direct database access, direct tool persistence, or multi-agent routing.
- Free-form arbitrary JSON Patch, template/Preview/Export changes, or Match
  Report recomputation.
- Provider choice, retention duration, raw prompt/output storage, and production
  launch until approved with Epic 5 operational controls.

## Source requirements

- `FR-12`: Evidence interview, structured Patch proposal, human review, explicit
  approval, immutable new Version, and regeneration.
- `NFR-1`: privacy, ownership, minimum provider disclosure, and non-disclosure.
- `NFR-2`: exact source, Evidence, prompt/schema/model, decision, and Version provenance.
- `NFR-3`: complete, accessible interview/review/approval states.
- `NFR-4`: explicit provider/validation/persistence failures without partial trusted state.
- `NFR-5`: versioned inputs/contracts and repeatable validation/evaluation.

## Global and cross-Epic references

- Architecture: `AD-1` through `AD-8`, `AD-11`, `AD-13` through `AD-19`.
- API/security/data/reliability: `API-STD-001` through `API-STD-007`,
  `SEC-STD-001` through `SEC-STD-007`, `DATA-STD-001` through
  `DATA-STD-008`, and `REL-STD-001` through `REL-STD-005`.
- Accessibility/testing: `A11Y-STD-001` through `A11Y-STD-003` and
  `TEST-STD-001` through `TEST-STD-007`.
- Epic 1: `E1-CONTRACT-VERSION-001`, `E1-COORD-VERSION-001`.
- Epic 2: `E2-CONTRACT-MATCH-001`, `E2-COORD-MATCH-001`.

## Epic package

| Artifact | Owns |
| --- | --- |
| [Business rules](business-rules.md) | Shared Evidence, Patch, provider, and approval invariants |
| [Contracts](contracts.md) | Interview, answer, provider, Patch, decision, apply, and errors |
| [Data and lifecycle](data-and-lifecycle.md) | Identities, provenance, immutability, states, and transactions |
| [Security and access](security-and-access.md) | Ownership, disclosure, injection, secrets, logs, retention, abuse |
| [UX and validation](ux-and-validation.md) | Interview, review, edit, approval, regeneration, and accessibility |
| [Test strategy](test-strategy.md) | Contract fixtures, adversarial corpus, quality, atomicity, and E2E |
| [Decisions](decisions.md) | Human-owned decisions and coordination records blocking readiness |

## Story packages

| Story | Package |
| --- | --- |
| 4.1 Start an Evidence interview | [Open](stories/4-1-start-an-evidence-interview/README.md) |
| 4.2 Answer Evidence questions | [Open](stories/4-2-answer-evidence-questions/README.md) |
| 4.3 Generate a Patch proposal | [Open](stories/4-3-generate-a-patch-proposal/README.md) |
| 4.4 Review, edit, or reject a Patch | [Open](stories/4-4-review-edit-or-reject-a-patch/README.md) |
| 4.5 Approve a Patch into a new CV Version | [Open](stories/4-5-approve-a-patch-into-a-new-cv-version/README.md) |
| 4.6 Regenerate a rejected or invalid Patch | [Open](stories/4-6-regenerate-a-rejected-or-invalid-patch/README.md) |

## Story map and ordering

```text
Epic 2 Match Report + Epic 1 CV Version
                 -> 4.1 Interview -> 4.2 Answers -> 4.3 Patch proposal
                                                    -> 4.4 Review/edit/reject
                                                       -> 4.5 Approve -> new CV Version
                                                       -> 4.6 Regenerate -> new proposal -> 4.4
```

## Epic Definition of Ready

- Cross-Epic Match Report and CV Version source checkpoints are approved.
- Every applicable decision has an owner, resolution, and dated evidence.
- Evidence, provider DTO, Patch allowlist/schema, lifecycle, stale/apply
  transaction, retention, audit, and evaluation contracts are frozen.
- Canonical ACs map to atomic tasks and evidence; shared boundaries have one
  coordination owner.

## Epic Definition of Done

- All six Stories are `done` with implementation and verification evidence.
- Provider output cannot become trusted state without server validation and
  explicit User approval.
- Source CV Version and prior Patch decisions remain immutable and reproducible.
- New Version creation and Patch application are atomic, idempotent, and
  ownership-safe.
- Adversarial provider, privacy, accessibility, failure, MySQL, evaluation, and
  critical browser checks pass against approved versions.
