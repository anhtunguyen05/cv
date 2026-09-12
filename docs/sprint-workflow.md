# BMAD Sprint Workflow

This workflow organizes CareerFitCV planning and delivery by sprint while
keeping Epic/Story content, lifecycle, sprint membership, and task coordination
in separate sources of truth.

## Usage boundary

For a Story-breakdown assignment, stop after the selected Story packages have
been reviewed and the planning PR has been opened. Do not implement product
code, start implementation branches, advance lifecycle, or record
implementation evidence during planning.

## Artifact ownership

| Artifact | Owns | Must not own |
| --- | --- | --- |
| `_bmad-output/planning-artifacts/epics.md` | Canonical Epic, Story, and AC intent | Sprint assignment or implementation status |
| `_bmad-output/planning-artifacts/epics/<epic-key>/` | Shared Epic planning and nested Story packages | Lifecycle or sprint membership |
| `_bmad-output/planning-artifacts/epics/<epic-key>/stories/<story-key>/` | One permanent Story content/task/evidence package | A duplicate Story lifecycle status |
| `_bmad-output/implementation-artifacts/sprint-status.yaml` | Epic and Story lifecycle | Sprint membership or task status |
| `_bmad-output/implementation-artifacts/sprints/<sprint-id>.md` | Sprint goal, dates, capacity, constraints, refinement keys, and committed keys | Story or task status |

Current Story status is always read from `sprint-status.yaml`. Current task
status is always read from the Story's `tasks.md`. A sprint charter links those
sources by Story key; it does not copy their values.

## Sprint charter

Create one charter per sprint under
`_bmad-output/implementation-artifacts/sprints/`:

```yaml
---
sprint_id: <stable sprint identifier>
title: <short sprint title>
status: draft
start: <YYYY-MM-DD>
end: <YYYY-MM-DD>
facilitator: <owner or unassigned>
goal: <one measurable sprint outcome>
refinement_stories: []
committed_stories: []
capacity_assumptions: []
---
```

Sprint lifecycle is separate from Story lifecycle and uses
`draft -> committed -> active -> review -> closed`. A sprint in `draft`,
`committed`, or `active` may become `cancelled` with a recorded reason and
decision owner. Do not reopen `closed` or `cancelled` charters.

Frontmatter is the only sprint membership source:

- `refinement_stories` contains canonical Story keys selected for planning.
- `committed_stories` contains only reviewed Story packages explicitly moved
  to `ready-for-dev` in the tracker.
- The body may explain selection and dependencies but does not maintain a
  second membership list.

Every operation names its `sprint_id`; never infer a current sprint by file
order or modification time. A Story may occur in at most one non-closed,
non-cancelled charter across both membership fields.

## 1. Prepare the sprint

1. Read `epics.md`, `sprint-status.yaml`, open action items, relevant
   architecture/product decisions, and existing Epic packages.
2. Define one measurable sprint goal before selecting Stories.
3. Record dates, facilitator, capacity assumptions, absences, and constraints.
4. Select canonical backlog keys. A Story may enter refinement while still at
   `backlog`.
5. Add accepted planning candidates to `refinement_stories`, not
   `committed_stories`.
6. Reject candidates whose dependencies, decisions, or expected scope make the
   goal unrealistic.

Candidate selection does not change Story lifecycle.

## 2. Break down Stories

1. For the supplied `sprint_id`, select one cohesive Story batch from
   `refinement_stories`.
2. Create or update each permanent package under its owning Epic:

   ```text
   epics/<epic-key>/stories/<story-key>/
   ├── README.md
   ├── requirements.md
   ├── contract.md
   ├── tasks.md
   └── verification.md
   ```

3. Follow [story-execution.md](story-execution.md) for behavior, contracts,
   tasks, dependencies, ownership, and verification.
4. Refine Stories concurrently when planning scopes do not conflict.
5. Run `bmad-review` on the complete Epic/Story package.
6. Keep unresolved material decisions explicit and leave tracker state at
   `backlog` until human approval.

There is no draft/published file move. Story content is edited in place; only
the lifecycle tracker advances.

## 3. Commit sprint scope

1. Confirm every selected Story package covers behavior, contract, backend,
   security, validation, frontend, integration, ACs, dependencies, and
   verification.
2. Confirm every material decision has an owner, approved resolution, and
   dated evidence.
3. Promote approved stable contract subsets into `docs/contracts/`; Story task
   fixtures consume them instead of redefining them.
4. Run `bmad-sprint-planning` as a dry run from the latest base. Resolve
   warnings, illegal states, and orphans.
5. The sprint integration owner explicitly sets approved Story keys to
   `ready-for-dev`, generates, and validates `sprint-status.yaml`.
6. Move the same keys from `refinement_stories` to `committed_stories`.
7. Confirm goal, capacity, dependencies, and coordination boundaries; then set
   the charter to `committed`.

One integration owner serializes charter membership and tracker generation.
Nested Story-folder existence is not an automatic readiness signal, and no
root-level Story Markdown copy is created.

Sprint commitment defines agreed scope; it does not require every committed
Story to start at once.

## 4. Run concurrent work

Set the charter to `active`, then apply the ownership, dependency,
branch/worktree, task-transition, and shared-boundary rules in
[story-execution.md](story-execution.md).

- Multiple Stories and independent tasks may be active concurrently.
- Each `doing` task has one owner, one non-`main` branch/worktree, declared
  scope, and satisfied dependencies.
- Overlapping contract, file, persistence, or domain work shares an approved
  coordination record.
- `bmad-build` synchronizes implementation lifecycle and evidence; ordinary
  sprint regeneration never downgrades progress.

## 5. Control sprint changes

- Do not add work silently after commitment.
- Record each added/removed Story, reason, decision owner, and impact on goal
  and capacity.
- Return independent discoveries to the canonical backlog.
- Replan when a dependency or scope change invalidates the goal.
- Keep charter membership synchronized with agreement while continuing to
  read lifecycle only from `sprint-status.yaml`.
- Serialize tracker writes through the sprint integration owner from the
  latest base revision.

Before commitment, validate that the charter ID is unique; `start <= end`;
status transition is legal; goal, facilitator, capacity, and membership are
complete; every key exists in `epics.md`; every committed key has one complete
nested Story package and tracker `ready-for-dev` or later; and no key belongs
to another open charter.

## 6. Review and close

1. Set the charter to `review` when the timebox ends or all committed work has
   a terminal result.
2. Combine membership from the charter, lifecycle from `sprint-status.yaml`,
   and task/evidence state from each Story package.
3. Demonstrate completed outcomes against the sprint goal; planned or partial
   behavior is not delivered evidence.
4. Record unfinished outcomes as `carried-over` with a destination sprint.
5. Record retrospective outcomes and track action items through BMAD's
   `action_items` section.
6. Set the charter to `closed` after outcomes and carry-over destinations are
   recorded.

## BMAD synchronization note

The generic sprint generator scans only root-level Markdown files when it
infers a `ready-for-dev` floor. CareerFitCV deliberately keeps Stories nested,
so lifecycle advancement must be explicit through the approved
`bmad-sprint-planning` flow. Do not add duplicate root files merely to trigger
inference.
