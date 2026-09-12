# Sprint Charters

Create one `<sprint-id>.md` file per sprint by following
[`docs/sprint-workflow.md`](../../../docs/sprint-workflow.md).

A charter records sprint goal, dates, capacity assumptions, constraints,
`refinement_stories`, and `committed_stories`. Frontmatter is the only sprint
membership list, and a story may belong to at most one non-closed,
non-cancelled charter. A charter must not copy story or task status:

- read epic and story lifecycle from `../sprint-status.yaml`;
- read task status and evidence from the story's `tasks.md` under its Epic
  package; and
- use one permanent story folder; do not create draft and published copies.

Do not add sprint or task entries under `development_status` in
`sprint-status.yaml`.
