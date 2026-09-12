# BMAD Story Breakdown Protocol

This protocol governs planning-only work that turns a canonical CareerFitCV
story into a concrete, reviewable package. It does not authorize product code,
infrastructure, CI, dependency, or runtime changes.

## Sources of truth

| Artifact | Authority |
| --- | --- |
| `_bmad-output/planning-artifacts/prds/prd-CareerFitCV-2026-09-01/` | Product requirements and business intent |
| `_bmad-output/planning-artifacts/architecture/architecture-CareerFitCV-2026-09-01/ARCHITECTURE-SPINE.md` | Architecture constraints and system boundaries |
| `_bmad-output/planning-artifacts/epics.md` | Canonical epic, story, and acceptance-criteria definitions |
| `_bmad-output/planning-artifacts/epics/<epic-key>/` | One Epic planning package with shared rules and nested Story packages |
| `_bmad-output/planning-artifacts/epics/<epic-key>/stories/<story-key>/` | One permanent Story package |
| `_bmad-output/implementation-artifacts/sprint-status.yaml` | Epic and Story lifecycle only |
| `_bmad-output/implementation-artifacts/sprints/<sprint-id>.md` | Sprint goal, capacity, constraints, and membership |

Sprint selection and lifecycle follow [sprint-workflow.md](sprint-workflow.md).
When BMAD artifacts disagree, record the conflict, keep the tracker unchanged,
and block `ready-for-dev` until the product owner resolves it.

## Permanent hierarchy

Epic and Story planning must stay in one navigable hierarchy:

```text
_bmad-output/planning-artifacts/epics/
└── <epic-key>/
    ├── README.md
    ├── business-rules.md
    ├── contracts.md
    ├── data-and-lifecycle.md
    ├── security-and-access.md
    ├── ux-and-validation.md
    ├── test-strategy.md
    ├── decisions.md
    └── stories/
        └── <story-key>/
            ├── README.md
            ├── requirements.md
            ├── contract.md
            ├── tasks.md
            └── verification.md
```

There is no `story-drafts/` directory and no separate published Story copy.
A Story folder never moves when its lifecycle changes. The Story package owns
content; `sprint-status.yaml` owns lifecycle.

## Planning boundary

Story-breakdown work may:

- read the repository and BMAD artifacts;
- analyze user behavior, business rules, interfaces, dependencies, and risk;
- create or update Epic and Story planning packages; and
- record unresolved decisions and review outcomes.

Story-breakdown work must not:

- implement or refactor application code;
- add dependencies or change runtime configuration;
- create CI, validation scripts, database migrations, or tests;
- advance lifecycle without human approval; or
- mark implementation tasks complete without implementation evidence.

Discovered implementation defects and unrelated improvements are separate
follow-up work, not hidden additions to a planning-only branch.

## Story readiness coverage

A Story can move to `ready-for-dev` only when every applicable concern is
explicit and no unresolved decision can change behavior, security, data
semantics, public contracts, dependencies, or verification. Mark a concern
`N/A` only with a reason.

### Behavior

- User, goal, trigger, preconditions, and state transitions.
- Happy path, alternate paths, cancellation, retry, and recovery.
- User-visible success and failure behavior.

### Contract

- Request fields, types, optionality, and normalization.
- Success and failure response shapes.
- HTTP status codes or equivalent outcomes.
- Stable error codes, field paths, headers, and user-safe messages.

### Backend

- Domain rules and invariants.
- Use-case and responsibility boundaries.
- Persistence reads/writes and ownership.
- Transactions, idempotency, concurrency, and side effects.
- Worker or external service interaction when applicable.

### Security

- Authentication, authorization, and cross-user isolation.
- Credential and sensitive-data handling.
- Enumeration and information-disclosure behavior.
- Abuse controls such as throttling when applicable.

### Validation

- Accepted formats and canonicalization.
- Field and cross-field rules.
- Uniqueness and conflict behavior.
- Boundary, malformed, duplicate, and concurrent cases.

### Frontend

- Form or interaction structure.
- Loading, submitting, success, empty, stale, and failure states.
- Accessible field/form errors and keyboard behavior.
- Navigation and state synchronization.

### Integration

- Frontend-to-API mapping and authentication headers/cookies.
- Response/error mapping into UI states.
- Retry, refresh, expiry, stale state, and duplicate submission.

### Verification

- Unit coverage for domain and validation rules.
- API feature/integration coverage for contracts and persistence.
- Frontend component coverage for interaction states.
- FE/API contract coverage.
- End-to-end coverage for critical paths and meaningful failures.

## Story package contract

Each Story uses exactly one folder. Its `README.md` frontmatter contains
identity and coordination metadata, but no lifecycle `status` field:

```yaml
---
story_key: <story-key>
title: <story title>
type: feature
created: <YYYY-MM-DD>
story_owner: unassigned
depends_on_stories: []
source_story: _bmad-output/planning-artifacts/epics.md
source_story_key: <story-key>
review_loop_iteration: 0
context: []
---
```

File ownership is fixed:

| File | Owns |
| --- | --- |
| `README.md` | Identity, frozen intent, references, canonical ACs, readiness summary, code map, and change log |
| `requirements.md` | Behavior, constraints, domain/backend rules, security, validation, frontend, integration, and edge cases |
| `contract.md` | Story-owned request, response, status/error, and FE/API contract slice |
| `tasks.md` | Task status, owner, branch/worktree, scope, dependencies, and coordination |
| `verification.md` | AC traceability, test layers, exit gate, and actual evidence rules |

Do not duplicate Epic-shared or Global rules inside Story files. Reference the
stable IDs at the lowest level that covers all consumers.

## Task contract

Every task in `tasks.md` uses this shape:

```markdown
- [ ] TASK-<story>-<nn>: <bounded outcome>
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `<task IDs or none>`
  - Covers: `<stable acceptance-criterion IDs>`
  - Scope: `<files, component, contract, or domain boundary>`
  - Coordination: `<coordination record IDs or none>`
  - Blocked by: `<task, decision, external dependency, or none>`
  - Outcome: `<observable deliverable>`
  - Acceptance: `<condition proving the task is complete>`
  - Verification: `<planned check; replace with evidence before done>`
```

Each task has one bounded, reviewable outcome for one owner. `Depends on` may
reference only tasks in the same Story and must be acyclic. A dependency is
satisfied only when its predecessor is `done` with acceptance and evidence.
Split independent outcomes, owners, or unrelated boundaries into separate
tasks.

The checkbox is consumed by `bmad-build`: use `[x]` only with `Status: done`.
The Story owner integrates task status and evidence; task owners report results
rather than editing the same task board concurrently.

## Team concurrency

There is no repository-wide single-active-Story or single-active-task lock.

- Multiple Stories and tasks may be active concurrently.
- During breakdown, tasks remain `todo` with owner and branch unassigned.
- Before `doing`, a task needs one owner, a non-`main` branch/worktree, declared
  scope, and all dependencies `done`.
- Tasks with overlapping files, API contracts, persistence, or domain scope
  reserve the shared boundary and reference the same coordination record.
- Independent tasks with satisfied dependencies may proceed in parallel.
- The Story owner owns cross-task integration and final acceptance; each task
  owner remains responsible for their bounded outcome.

Use this shape for a shared-boundary decision:

```markdown
### COORD-<scope>-<nn>: <shared boundary>

- Tasks: `<task IDs across affected stories>`
- Decision owner: `<one owner>`
- Resolution: `<agreed contract or reservation>`
- Sequence/merge rule: `<ordering and integration rule>`
```

Task transitions are `todo -> doing -> review -> done`. A task in `todo`,
`doing`, or `review` may move to `blocked`; record the blocker and previous
state before resuming. Do not skip states.

## Lifecycle synchronization

Story lifecycle exists only in `sprint-status.yaml`. This repository's nested
Story folders are intentionally not used as automatic readiness signals.

- Breakdown/review leaves the Story at `backlog`.
- Human approval resolves material decisions and accepts the complete package.
- The sprint integration owner then runs `bmad-sprint-planning` from the latest
  base, explicitly setting the approved key to `ready-for-dev`, and validates
  the tracker.
- Later transitions are synchronized by `bmad-build` with implementation and
  verification evidence.

Do not hand-edit generated lifecycle entries. BMAD's root Markdown-file
detection is a generic fallback and is not the publication mechanism for this
nested project structure.

## Change control

- Never change canonical Story intent silently during breakdown.
- A missing rule affecting behavior, security, data, public contracts, or
  verification requires a product-owner decision.
- Assumptions remain explicit and never become approved requirements by prose.
- Independent discoveries become separate backlog items.
- A reopened Story remains in the same folder. Record the reason and approval
  in its change log, then use the confirmed sprint-status fix flow if lifecycle
  must move backward.
- Story-breakdown PRs identify Story keys, open/resolved decisions, hierarchy
  changes, and planning-only scope.

## BMAD planning workflow

1. Supply a `sprint_id` and select keys listed in its `refinement_stories`.
2. Read canonical Story/AC intent, PRD, architecture, Global standards, and
   the owning Epic package.
3. Create or resume each permanent `stories/<story-key>/` package.
4. Analyze all eight readiness concerns; record material gaps in the Epic or
   Story decision register without inventing approval.
5. Produce atomic tasks, dependencies, coordination records, and AC evidence
   mapping in the fixed five-file structure.
6. Run `bmad-review` on the whole Epic/Story package with all applicable lenses.
7. Apply review corrections and open one cohesive planning PR for the accepted
   Epic or Story batch.
8. Stop. Do not implement code or advance lifecycle.
9. After human approval, the sprint integration owner explicitly synchronizes
   approved Story keys through `bmad-sprint-planning` and validates the tracker.
