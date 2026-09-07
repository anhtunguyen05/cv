# BMAD Story Breakdown Protocol

This protocol governs planning-only work that turns an approved CareerFitCV
story into concrete, reviewable tasks. It does not authorize changes to product
code, infrastructure, CI, dependencies, or runtime configuration.

## Sources of truth

| Artifact | Authority |
| --- | --- |
| `_bmad-output/planning-artifacts/prds/prd-CareerFitCV-2026-09-01/` | Product requirements and business intent |
| `_bmad-output/planning-artifacts/architecture/architecture-CareerFitCV-2026-09-01/ARCHITECTURE-SPINE.md` | Architecture constraints and system boundaries |
| `_bmad-output/planning-artifacts/epics.md` | Canonical epic, story, and acceptance-criteria definitions |
| `_bmad-output/implementation-artifacts/sprint-status.yaml` | Epic and story lifecycle only |
| `_bmad-output/implementation-artifacts/<story-key>.md` | BMAD story analysis and task breakdown |

`docs/implementation-plan.md` and `docs/implementation/` are legacy phase
references. Do not use them to select, sequence, or decompose current work.
When current BMAD artifacts disagree, record the conflict, keep the tracker state
unchanged, and block `ready-for-dev` until the product owner resolves it.

## Planning boundary

Story-breakdown work may:

- read the repository and BMAD artifacts;
- analyze user behavior, business rules, interfaces, dependencies, and risk;
- create or update BMAD planning and story artifacts; and
- update story planning status after review.

Story-breakdown work must not:

- implement or refactor application code;
- add dependencies or change runtime configuration;
- create CI, validation scripts, database migrations, or tests; or
- mark implementation tasks complete without implementation evidence.

Discovered implementation defects and unrelated improvements are recorded as
follow-up work; they are not fixed on a planning-only branch.

## BMAD planning workflow

1. Select a story key that exactly matches a non-epic entry in `epics.md` and
   `sprint-status.yaml`.
2. Read the canonical story, PRD requirements, architecture boundaries, and
   relevant current technical documents.
3. Identify gaps in user behavior and business rules. Record material gaps as
   decisions required from the product owner; do not invent them.
4. Create or resume `<story-key>.md` with status `draft`. Preserve any later
   status unless the product owner explicitly reopens the story.
5. Complete the readiness coverage and produce atomic tasks with dependencies,
   scope, acceptance, and planned verification.
6. Run `bmad-review` over the story artifact using every applicable document
   lens, including adversarial, edge-case, structure, and prose review.
7. Present the story artifact for human review and apply approved corrections.
8. After approval, set the story artifact to `ready-for-dev`, then run
   `bmad-sprint-planning` to update and validate `sprint-status.yaml` through
   its deterministic workflow. Do not hand-maintain a conflicting status.
9. Commit only planning artifacts and open one reviewable planning PR for the
   story unless the team explicitly approves a cohesive multi-story batch.
10. Stop. Do not invoke `bmad-build`; product implementation begins in a
    separate branch and workflow.

## Story readiness coverage

A story can move to `ready-for-dev` only when each applicable category below is
documented explicitly and no unresolved decision can change behavior, security,
data semantics, a public contract, dependencies, or verification. Mark a
category `N/A` with a reason when it genuinely does not apply.

### Behavior

- user, goal, trigger, and preconditions;
- happy path and state transitions;
- alternate paths, cancellation, retry, and recovery; and
- success and failure behavior visible to the user.

### Contract

- request fields, types, required/optional rules, and normalization;
- success and failure response shapes;
- HTTP status codes or equivalent interface outcomes; and
- stable error codes and user-safe messages.

### Backend

- domain rules and invariants;
- application use case and responsibility boundaries;
- persistence reads and writes;
- transaction, idempotency, concurrency, and side effects; and
- external service or worker interaction where applicable.

### Security

- authentication and authorization;
- ownership and cross-user isolation;
- credential and sensitive-data handling;
- enumeration and information-disclosure behavior; and
- abuse protection such as throttling or rate limiting where applicable.

### Validation

- accepted formats and normalization;
- field and cross-field rules;
- uniqueness and conflict behavior; and
- boundary, malformed, duplicate, and concurrent input cases.

### Frontend

- form or interaction structure;
- loading, submitting, success, empty, and failure states;
- field-level and form-level error presentation;
- accessibility and keyboard behavior; and
- navigation and state synchronization.

### Integration

- frontend-to-API mapping;
- authentication and request headers;
- response and error mapping into UI states; and
- retry, refresh, stale-state, and duplicate-submit behavior.

### Verification

- unit coverage for domain and validation rules;
- backend feature or integration coverage for contracts and persistence;
- frontend component coverage for interaction states;
- integration coverage across frontend and API; and
- end-to-end scenarios for the critical user path and meaningful failures.

## Story artifact

Create one file per story at
`_bmad-output/implementation-artifacts/<story-key>.md`. Base it on
`.agents/skills/bmad-build/spec-template.md` so later BMAD implementation can
consume it without translation, but do not run the implementation workflow
during planning-only work. Extend the template with this coordination metadata:

```yaml
---
story_key: <story-key>
title: <story title>
type: feature
created: <YYYY-MM-DD>
status: draft
story_owner: unassigned
depends_on_stories: []
source_story: _bmad-output/planning-artifacts/epics.md
source_story_key: <story-key>
review_loop_iteration: 0
context: []
---
```

The body must contain:

1. the template's human-owned intent, boundaries, and non-goals;
2. assumptions and unresolved decisions;
3. the eight-category readiness analysis;
4. canonical acceptance criteria copied without changing intent and assigned
   stable IDs in the form `AC-<story-key>-<nn>`;
5. an ordered task breakdown;
6. a dependency and concurrency map;
7. coordination records for overlapping work; and
8. planned verification coverage.

The story artifact's `status` is required by BMAD dispatch but is not a second
tracker. `sprint-status.yaml` remains authoritative. `draft` maps to tracker
`backlog`; other states must match. A mismatch blocks dispatch until
`bmad-sprint-planning` reconciles and validates the tracker.

## Task contract

Use this shape for every task:

```markdown
### TASK-<story>-<nn>: <bounded outcome>

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

Each task must:

- produce one bounded outcome;
- be small enough for one owner and one reviewable change;
- declare dependencies rather than relying on list position alone;
- identify the boundary it is allowed to change;
- trace to at least one stable story acceptance-criterion ID; and
- include a completion condition and planned verification.

Every dependency must reference an existing task in the same story. Reject
self-dependencies, missing dependencies, and cycles. A dependency is satisfied
only when it is `done`.

Split a task when it combines independent outcomes, has different owners, or
crosses unrelated boundaries. Keep a cross-layer user outcome in one story,
but separate its backend, frontend, integration, and verification work into
assignable tasks when their dependencies allow it.

## Team concurrency

There is no repository-wide single-active-story or single-active-task lock.

- Multiple stories may be `in-progress` or `review` concurrently.
- Multiple tasks may be `doing` concurrently, including tasks within the same
  story, when their dependencies are satisfied.
- During breakdown, a task remains `todo`, and its owner and branch/worktree may
  remain `unassigned`.
- Before setting any task to `doing`, the story must have exactly one
  `story_owner`; the task must have one owner, a non-`main` branch or worktree,
  declared scope, and only `done` dependencies.
- An unmet `depends_on_stories` entry blocks story implementation unless the
  story artifacts record an approved contract checkpoint that makes the tasks
  independent.
- Tasks with overlapping file, API contract, persistence, or domain scope must
  reserve the shared boundary before `doing` and reference a coordination
  record in every affected story. Each record names the tasks, shared boundary,
  decision owner, resolution, and sequencing or merge rule.
- A story owner coordinates acceptance criteria, cross-task integration, and
  final readiness; task owners remain responsible for their bounded outcomes.

Allowed task transitions are `todo -> doing -> review -> done`. A task in
`todo`, `doing`, or `review` may move to `blocked` and must record both the
blocker and its previous state before resuming. Do not skip states. `done`
requires its acceptance condition and verification evidence. A blocked task
does not prevent independent tasks from progressing.

## Change control

- Never change canonical story intent silently while breaking it into tasks.
- A missing business rule that changes user-visible behavior, security, data
  semantics, or a public contract requires a product-owner decision.
- Record assumptions explicitly and do not treat an unresolved assumption as
  an approved requirement.
- Record independent stories or improvements separately from the current story
  artifact; do not hide them inside a convenient task.
- Story breakdown PRs must identify the story keys, summarize resolved and open
  decisions, and confirm that no product code was changed.
