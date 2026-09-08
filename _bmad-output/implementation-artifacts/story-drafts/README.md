# Story Drafts

Keep unapproved BMAD story breakdowns in this directory using
`<story-key>.md`. Follow [`docs/story-execution.md`](../../../docs/story-execution.md)
and [`docs/sprint-workflow.md`](../../../docs/sprint-workflow.md).

After human approval:

1. have the sprint integration owner set the story artifact status to
   `ready-for-dev`;
2. publish it to `../<story-key>.md` and remove the draft copy;
3. move the story key from the charter's `refinement_stories` to
   `committed_stories`; and
4. run `bmad-sprint-planning` from the latest base revision to regenerate and
   validate `../sprint-status.yaml`.

Do not place draft story files at the implementation-artifact root. BMAD uses
the existence of a matching root Markdown file as a readiness signal.
