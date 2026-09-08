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
| `_bmad-output/implementation-artifacts/sprints/<sprint-id>.md` | Sprint goal, capacity assumptions, constraints, refinement story keys, and committed story keys |
| `_bmad-output/implementation-artifacts/story-drafts/<story-key>.md` | Unapproved BMAD story analysis and task breakdown |
| `_bmad-output/implementation-artifacts/<story-key>.md` | Approved BMAD story contract and task board |

Sprint selection and lifecycle follow [`sprint-workflow.md`](sprint-workflow.md).
When current BMAD artifacts disagree, record the conflict, keep the tracker
state unchanged, and block `ready-for-dev` until the product owner resolves it.

## Planning boundary

Story-breakdown work may:

- read the repository and BMAD artifacts;
- analyze user behavior, business rules, interfaces, dependencies, and risk;
- create or update BMAD planning and story artifacts; and
- record review decisions in draft story artifacts.

Story-breakdown work must not:

- implement or refactor application code;
- add dependencies or change runtime configuration;
- create CI, validation scripts, database migrations, or tests; or
- mark implementation tasks complete without implementation evidence.

Discovered implementation defects and unrelated improvements are recorded as
follow-up work; they are not fixed on a planning-only branch.

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

Create an unapproved story file at
`_bmad-output/implementation-artifacts/story-drafts/<story-key>.md`. Base it on
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

The story artifact's `status` is dispatch metadata that mirrors, but does not
replace, the authoritative tracker entry. Use this mapping:

| Story artifact | `sprint-status.yaml` |
| --- | --- |
| `draft` in `story-drafts/` | `backlog` |
| `ready-for-dev` | `ready-for-dev` |
| `in-progress` | `in-progress` |
| `in-review` | `review` |
| `done` | `done` |

A draft remains in the nested directory. After approval, publish exactly one
root-level `<story-key>.md`; the existence of this file is the BMAD readiness
signal. `sprint-status.yaml` remains authoritative, and any mismatch blocks
dispatch. Root-file detection can establish only the `ready-for-dev` floor;
later transitions are synchronized by `bmad-build`. Every tracker entry at
`ready-for-dev` or later must have exactly one matching root story artifact.

## Task contract

Use this shape for every task:

```markdown
## Tasks & Acceptance

**Execution:**

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

Each task must define one bounded, reviewable outcome for one owner; its allowed
boundary; at least one acceptance-criterion ID; completion conditions; and
planned verification. `Depends on` may reference only existing tasks in the
same story: reject missing, self, or cyclic dependencies, and treat only `done`
dependencies as satisfied. Split independent outcomes, owners, or unrelated
boundaries into separate tasks. Preserve one cross-layer story while separating
assignable backend, frontend, integration, and verification work where
dependencies allow.

The checkbox is the format consumed by `bmad-build`: use `[x]` only when the
task status is `done`; use `[ ]` for every other status. A story owner maintains
checkboxes, task status, and verification evidence on one story-coordination
branch or worktree. Task owners report results to that owner rather than making
parallel edits to the same task board.

## Team concurrency

There is no repository-wide single-active-story or single-active-task lock.

- Multiple stories may be `in-progress` or tracker `review` concurrently.
- Multiple tasks may be `doing` concurrently, including tasks within the same
  story, when their dependencies are satisfied.
- During breakdown, a task remains `todo`, and its owner and branch/worktree may
  remain `unassigned`.
- Before a task enters `doing`, the story must have exactly one `story_owner`.
  The task must have one owner, a non-`main` branch or worktree, a declared
  scope, and dependencies that are all `done`.
- An unmet `depends_on_stories` entry blocks story implementation unless the
  story artifacts record an approved contract checkpoint that makes the tasks
  independent.
- Tasks with overlapping file, API contract, persistence, or domain scope must
  reserve the shared boundary before `doing` and reference a coordination
  record in every affected story.
- A story owner coordinates acceptance criteria, cross-task integration, and
  final readiness; task owners remain responsible for their bounded outcomes.
  The story owner also owns the story-coordination branch and integrates task
  status and evidence updates.

Use this shape for every shared-boundary decision:

```markdown
### COORD-<story>-<nn>: <shared boundary>

- Tasks: `<task IDs across affected stories>`
- Decision owner: `<one owner>`
- Resolution: `<agreed contract or reservation>`
- Sequence/merge rule: `<ordering and integration rule>`
```

Allowed task transitions are `todo -> doing -> review -> done`. A task in
`todo`, `doing`, or `review` may move to `blocked`. Before the task resumes,
record its blocker and previous state. Do not skip states. The `done` status
requires the task's acceptance condition and verification evidence. A blocked
task does not prevent independent tasks from progressing.

## Change control

- Never change canonical story intent silently while breaking it into tasks.
- A missing business rule that changes user-visible behavior, security, data
  semantics, or a public contract requires a decision from the product owner.
- Record assumptions explicitly and do not treat an unresolved assumption as
  an approved requirement.
- Record independent stories or improvements separately from the current story
  artifact; do not hide them inside a convenient task.
- Story breakdown PRs must identify the story keys, summarize resolved and open
  decisions, and confirm that no product code was changed.

Reopening an approved story is not a normal lifecycle transition. Prefer a new
story that preserves the delivered contract. If correction requires reopening,
obtain explicit product-owner approval, record the reason in the change log,
move the single root artifact back to `story-drafts/`, use the confirmed
`bmad-sprint-planning` fix flow to change the tracker, and validate before any
dispatch. Never retain simultaneous draft and root copies.

## BMAD planning workflow

1. Supply a `sprint_id`, then select a story key listed in that charter's
   `refinement_stories`. The key must exactly match one non-epic entry in both
   `epics.md` and `sprint-status.yaml`.
2. Read the canonical story, PRD requirements, architecture boundaries, and
   relevant current technical documents.
3. Identify gaps in user behavior and business rules. Record material gaps as
   decisions required from the product owner; do not invent them.
4. Create or resume `story-drafts/<story-key>.md` with status `draft`. If a root
   artifact already exists, stop and follow the approved reopening procedure.
5. Complete the readiness coverage and produce atomic tasks with dependencies,
   scope, acceptance, and planned verification.
6. Run `bmad-review` on the story artifact using every applicable document
   lens, including the adversarial, edge-case, structure, and prose lenses.
7. Present the story artifact for human review and apply approved corrections.
8. Open one reviewable planning PR containing the draft story artifact unless
   the team explicitly approves a cohesive multi-story batch. Identify the
   story keys, resolved and open decisions, and planning-only scope.
9. Stop. Do not publish the draft, update shared sprint artifacts, or invoke
   `bmad-build`. After approval, the sprint integration owner publishes the
   root artifact, moves its key from refinement to commitment, runs
   `bmad-sprint-planning`, and validates the tracker in a serialized integration
   change.
