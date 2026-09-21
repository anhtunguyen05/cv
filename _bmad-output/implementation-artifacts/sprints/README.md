# Sprint Charters

Create one `<sprint-id>.md` file per sprint by following
[`docs/sprint-workflow.md`](../../../docs/sprint-workflow.md).

A charter records sprint goal, dates, capacity assumptions, constraints,
`refinement_stories`, and `committed_stories`. Frontmatter is the only sprint
membership list, and a story may belong to at most one non-closed,
non-cancelled charter. Cancelled charters retain their former membership as
historical data; ignore those lists when checking current membership and
follow the successor IDs in their frontmatter. A charter must not copy story
or task status:

- read epic and story lifecycle from `../sprint-status.yaml`;
- read task status and evidence from the story's `tasks.md` under its Epic
  package; and
- use one permanent story folder; do not create draft and published copies.

Do not add sprint or task entries under `development_status` in
`sprint-status.yaml`.

## External issue mapping

Story task keys such as `TASK-1-1-01` are permanent cross-references for GitHub
Issues or Jira. The task's `tasks.md` is the task-level source of truth. When an
external issue is explicitly requested and the platform is selected, record
the platform issue key and direct URL with that exact task entry. Put the
permanent task key and canonical Story key in the issue title or body, and put
the issue key and URL in the task entry. Do not create or guess external IDs or
links before that request.

Use this optional block under the relevant task only after an issue exists:

```text
External issue:
  platform: GitHub | Jira
  key: <org/repo#123 | PROJECT-123>
  url: <direct issue URL>
  verified_by: <owner>
  verified_at: <YYYY-MM-DD>
  verification_result: pass | fail
```

Verify the mapping in both directions: open the URL recorded in the task and
confirm the issue contains the same task and Story keys; then follow the task
key from the issue back to that exact Story task entry and confirm its external
key and URL match. Record `pass` only when both directions match. Record `fail`
when either direction is missing or mismatched, and fix the mapping before
relying on that external issue for tracking. Repeat the check if the issue key,
URL, task key, or Story key changes. This check is performed when the external
issue is created or its mapping changes. It does not copy task or Story
lifecycle into a sprint charter or make GitHub or Jira a canonical status
source. By default, create one external issue per canonical task; if a platform
limitation or explicit user choice requires one issue to represent multiple
tasks, list every permanent task key and Story key in the issue and record the
shared mapping in each affected task entry.

## Change control

Before a sprint is committed, changing refinement membership requires an
explicit scope review: record the added or removed Story, the reason, decision
owner, and effect on the sprint outcome, capacity, and dependencies. Update
only the charter's `refinement_stories`; write the record under a dated
  `## Scope change log` section in the same charter. Start each entry with a
  `YYYY-MM-DD` heading and record the added or removed Story, reason, decision
  owner, and goal/capacity/dependency impact, so the frontmatter remains the
  sole membership source and the rationale stays reviewable. Lifecycle remains
  in `sprint-status.yaml`. After commitment, use the change-control steps in
[`docs/sprint-workflow.md`](../../../docs/sprint-workflow.md) and record the
decision owner, reason, membership change, goal/capacity impact, and any
replanning. Independent newly discovered work returns to the canonical
backlog. Adding a newly scoped task within an approved Story uses a new
permanent `TASK-<story>-NN` key in that Story's `tasks.md`; do not renumber
existing tasks or silently change charter membership.
