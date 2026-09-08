# BMAD Sprint Workflow

This workflow organizes CareerFitCV planning and delivery by sprint. It keeps
sprint scope, story lifecycle, and task coordination in separate BMAD artifacts
so a team can work concurrently without creating competing status trackers.

## Usage boundary

For a story-breakdown assignment, stop after the relevant draft stories have
been reviewed and the planning PR has been opened. Do not implement product
code, start implementation branches, publish an unapproved root story artifact,
or record implementation evidence during the planning workflow.

## Artifact ownership

| Artifact | Owns | Must not own |
| --- | --- | --- |
| `_bmad-output/planning-artifacts/epics.md` | Canonical stories and acceptance intent | Sprint assignment or implementation status |
| `_bmad-output/implementation-artifacts/sprint-status.yaml` | Epic and story lifecycle | Sprint membership or task status |
| `_bmad-output/implementation-artifacts/sprints/<sprint-id>.md` | Sprint goal, dates, capacity assumptions, constraints, refinement story keys, and committed story keys | Story or task status |
| `_bmad-output/implementation-artifacts/story-drafts/<story-key>.md` | Unapproved story analysis and task breakdown | Ready-for-development state |
| `_bmad-output/implementation-artifacts/<story-key>.md` | Approved story contract, tasks, ownership, coordination, and evidence | Sprint membership |

Current story status is always read from `sprint-status.yaml`. Current task
status is always read from the approved story artifact. A sprint charter links
those sources by story key; it does not copy their values.

## Sprint charter

Create one charter per sprint under
`_bmad-output/implementation-artifacts/sprints/`. Use this frontmatter:

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

Sprint status is local to the charter and uses
`draft -> committed -> active -> review -> closed`. A sprint in `draft`,
`committed`, or `active` may instead move to the terminal state `cancelled` when
the charter records the reason and decision owner. Do not reopen `closed` or
`cancelled` charters; create a new charter that references the earlier sprint.
Sprint status does not belong under `development_status` in
`sprint-status.yaml`.

Frontmatter is the sole source for sprint membership. `refinement_stories`
contains story keys selected for planning but not committed for implementation.
`committed_stories` contains only approved story keys with matching root story
artifacts. The body may explain the selection rationale by referencing those
keys, but it must not maintain another membership list.

The charter body records:

- a measurable sprint goal and done signal;
- why each frontmatter story key supports the goal;
- capacity assumptions and planned absences;
- cross-story dependencies and integration checkpoints;
- shared file, contract, persistence, or domain boundaries that need
  coordination;
- accepted scope changes, removed work, and carry-over destinations; and
- sprint review and retrospective outcomes.

Every planning or delivery operation must name its `sprint_id`; do not infer a
"current" sprint from file order or modification time. A story key may appear
in at most one non-closed, non-cancelled charter across both membership fields.

## 1. Prepare the sprint

1. Read `epics.md`, `sprint-status.yaml`, open action items, and relevant
   architecture or product decisions.
2. Define one sprint goal before selecting stories.
3. Record sprint dates, facilitator, capacity assumptions, and constraints.
4. Select candidate story keys from the canonical backlog. A story may be
   selected for refinement before it is `ready-for-dev`.
5. Add accepted planning candidates to `refinement_stories`; do not add them to
   `committed_stories` yet.
6. Reject a candidate when its dependency, product decision, or expected scope
   makes the sprint goal or capacity assumption unrealistic.

No story status changes during candidate selection.

## 2. Break down stories

1. For the explicitly supplied `sprint_id`, select a story key from
   `refinement_stories` and create its unapproved file only under
   `story-drafts/`.
2. Follow [`story-execution.md`](story-execution.md) for behavior analysis,
   acceptance criteria, tasks, dependencies, ownership, and verification plans.
3. Refine different stories concurrently when their planning scopes do not
   conflict.
4. Run `bmad-review` on every draft. Keep material unresolved decisions in the
   draft and keep the tracker entry at `backlog`.
5. Obtain human approval before publishing a story contract.

The BMAD sprint generator scans for Markdown files only in the implementation
artifact root. Keeping drafts in the nested directory prevents an incomplete
story from being inferred as `ready-for-dev`.

## 3. Commit sprint scope

1. Set each approved draft selected for delivery to `ready-for-dev`, then
   publish it to `_bmad-output/implementation-artifacts/<story-key>.md`.
2. Verify that the published file has valid BMAD frontmatter and complete
   readiness coverage. File existence alone is not approval, even though the
   generator uses it as a `ready-for-dev` floor.
3. Run `bmad-sprint-planning` as a dry run first. Resolve warnings, illegal
   states, and orphans before writing.
4. Run the normal BMAD generation and validation workflow. Do not hand-edit
   generated lifecycle entries.
5. Move the approved key from `refinement_stories` to `committed_stories`.
   Leave planning-only or unapproved keys under `refinement_stories`; they are
   not implementation commitments.
6. Confirm that the sprint goal, capacity, dependencies, and coordination
   boundaries are accepted; then set the charter to `committed`.

One sprint integration owner serializes publication, charter membership
changes, and `sprint-status.yaml` regeneration. Parallel story-breakdown PRs
change draft artifacts only; after their approval, the integration owner
publishes them and refreshes shared sprint artifacts from the latest base.

Sprint commitment does not require every selected story to be implemented at
once. It defines the team's agreed scope and coordination boundary.

## 4. Run concurrent work

Set the charter to `active`, then apply the ownership, dependency,
branch/worktree, scope, status-transition, and shared-boundary rules in
[`story-execution.md`](story-execution.md). Record blockers and resolution
conditions, then re-check whether the sprint goal remains achievable;
independent work continues when its dependencies are satisfied.

Root story-file detection synchronizes only the `ready-for-dev` floor. During
implementation, `bmad-build` owns advanced story transitions and its sprint
sync maps story artifact `in-review` to tracker `review`. Ordinary sprint
regeneration never infers advanced states and never downgrades progress.

## 5. Control sprint changes

- Do not add work silently after commitment.
- Record every added or removed story key, reason, decision owner, and impact on
  the sprint goal or capacity.
- Return independent discoveries to the canonical backlog rather than hiding
  them inside a current task.
- Replan when a dependency or scope change invalidates the sprint goal.
- Keep the charter's story list synchronized with agreed sprint membership, but
  continue reading lifecycle state from `sprint-status.yaml`.
- Validate a charter before commitment: its ID is unique; `start <= end`; its
  status transition is legal; its goal, facilitator, capacity assumptions, and
  membership are non-empty; every story key exists in `epics.md`; every
  committed key has exactly one approved root story artifact; and no story is
  assigned to another non-closed, non-cancelled charter.
- Serialize writes to `sprint-status.yaml` through the sprint integration owner
  and regenerate from the latest base revision.

## 6. Review and close

1. Set the charter to `review` when the sprint timebox ends or all committed
   work reaches a terminal result.
2. Combine sprint membership from the charter, story lifecycle from
   `sprint-status.yaml`, and task evidence from approved story artifacts.
3. Demonstrate completed outcomes against the sprint goal. Do not count planned
   or partially verified behavior as delivered.
4. Preserve unfinished keys in the original charter and their current lifecycle
   state. Record each outcome as `carried-over` with a destination sprint ID;
   the destination may not commit the key until the original charter closes.
5. Record retrospective decisions and summaries in the charter. Track
   actionable follow-ups only through the BMAD retrospective workflow and the
   `action_items` section of `sprint-status.yaml`.
6. Set the charter to `closed` after outcomes and carry-over destinations are
   recorded.
