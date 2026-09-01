# Implementation Work Packages

This folder turns [`../implementation-plan.md`](../implementation-plan.md) into
phase-level implementation packages. The parent plan defines the product
sequence and architectural constraints; each phase folder defines the work
that can be executed and reviewed independently.

## Phase map

| Phase | Folder | Outcome | Depends on |
| --- | --- | --- | --- |
| 0 | `phase-0-foundation/` | Stable app shell, API boundary, and verification baseline | Current scaffold |
| 1 | `phase-1-cv-versioning/` | Structured CV profile and immutable versions | Phase 0 |
| 2 | `phase-2-jd-matching/` | JD intake and deterministic match report | Phase 1 |
| 3 | `phase-3-ai-patches/` | Evidence interview and reviewable patches (post-MVP) | Phase 2 |
| 4 | `phase-4-preview-export/` | Template preview and export (MVP) | Phase 1 CV Version foundation |
| 5 | `phase-5-hardening/` | Observability, safety, and provider hardening | Phase 4 |

Every phase contains:

- `README.md`: implementation design, boundaries, file plan, data/API details,
  tests, and exit criteria.
- `github-issues.md`: GitHub-ready issue breakdown. Each issue should normally
  be one pull request or one small, reviewable batch.

The package numbers retain the existing folder names. The MVP execution order
is `0 → 1 → 2 → 4`; Phase 3 is post-MVP AI work and follows Phase 4. Phase 5
follows the capability work it hardens.

**execution_order:** `0 → 1 → 2 → 4 → 3 → 5`

Do not interpret the numeric folder names as delivery order.

## Recommended execution protocol

1. Review the phase README and all issue dependencies.
2. Execute the first three unblocked issues as a batch.
3. Run the verification commands in the phase README.
4. Stop for review at the phase checkpoint.
5. Update the issue checkboxes and only then start the next batch.

Do not start a later phase merely because its folder exists. A phase is ready
when its declared dependencies' exit criteria have passed and its unresolved
decisions have been recorded in [`../decisions.md`](../decisions.md). Phase 4
does not depend on Phase 3; it can complete the MVP after the CV Version
foundation is available.

## Status convention

- `planned`: documented, not started
- `in-progress`: implementation is underway
- `blocked`: a named dependency or decision prevents progress
- `done`: code, tests, manual verification, and documentation are complete

The current repository status for all packages is `planned`; the folders do
not imply that the product functionality already exists.
