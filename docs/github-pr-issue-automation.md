# GitHub PR Task Issue Automation

The workflow at `.github/workflows/create-pr-task-issues.yml` creates GitHub
Issues when a pull request is opened. It runs only for the `opened` event; PR
reopen, synchronization, and later edits do not trigger it. A PR must include
one marker in its description to select configured tasks:

```markdown
<!-- issue-tasks: story-1-1 -->
```

The marker value selects a group from `.github/pr-issue-tasks.json`. Unknown or
repeated markers fail without creating issues. PRs without a marker are left
alone. A group contains one to three task records. Each record creates at most
one issue; `create: false` records are skipped. The workflow does not create
child issues.

## Tracking boundaries

- Canonical task definitions and task lifecycle remain in the Story's
  `tasks.md`. A canonical manifest record references the permanent task key,
  Story key, and exact `tasks.md` path. The workflow checks that the task key
  exists in that file before creating any issue.
- PR-specific follow-up work may use a namespaced `PR-...` key with
  `source: "ad_hoc"` and a reason. Such a record does not add a Story, task, or
  sprint membership to BMAD. Promote it into the canonical backlog only if it
  becomes product scope or affects acceptance criteria.
- The manifest defines automation selection and issue text. It does not own
  task lifecycle or replace the canonical task package.
- For canonical tasks, the workflow posts the issue URLs to the PR and embeds
  the task key and Story key in each issue. Record the created issue key and
  URL in that task's `tasks.md` External issue block, following the
  [sprint issue mapping rules](../_bmad-output/implementation-artifacts/sprints/README.md#external-issue-mapping).

## Configured Story 1.1 group

```json
{
  "version": 1,
  "groups": {
    "story-1-1": [
      {
        "key": "TASK-1-1-01",
        "source": "canonical",
        "story": "1-1-register-an-account",
        "task_file": "_bmad-output/planning-artifacts/epics/epic-1-trusted-cv/stories/1-1-register-an-account/tasks.md",
        "create": true,
        "title": "Freeze registration and User contract fixtures",
        "labels": []
      },
      {
        "key": "TASK-1-1-02",
        "source": "canonical",
        "story": "1-1-register-an-account",
        "task_file": "_bmad-output/planning-artifacts/epics/epic-1-trusted-cv/stories/1-1-register-an-account/tasks.md",
        "create": true,
        "title": "Deliver registration and account backend",
        "labels": []
      },
      {
        "key": "TASK-1-1-03",
        "source": "canonical",
        "story": "1-1-register-an-account",
        "task_file": "_bmad-output/planning-artifacts/epics/epic-1-trusted-cv/stories/1-1-register-an-account/tasks.md",
        "create": true,
        "title": "Deliver and verify registration journey",
        "labels": []
      }
    ]
  }
}
```

This group maps to the three canonical tasks in Story 1.1. Story 1.1 currently
remains `backlog` and its README records planning blockers; issue creation only
provides tracking and does not make the Story `ready-for-dev` or authorize
implementation. `labels` are optional; labels supplied there must already
exist in the GitHub repository, and the workflow checks them before creating
any issue. Use unique keys within each group. For ad hoc records, use a key
such as `PR-OPS-001` and provide a short reason. Set `create: false` for a task
that should be skipped for this group.

## Workflow safety and retry behavior

The workflow uses `pull_request_target` so it can create issues for fork PRs.
It checks out the PR base commit, where the trusted workflow configuration and
script live, and never checks out or executes PR-head code. It uses the
repository `GITHUB_TOKEN` with read access to contents and pull requests and
write access to Issues. Keep workflow changes under normal review.

Each created issue has a hidden marker containing the PR number and task key.
Before creating an issue, the script checks all existing issues, including
closed issues, for that marker. This makes manual reruns and retries
idempotent. Concurrency is serialized per PR. The PR summary comment is also
updated in place when it already exists.

## Maintaining the registry

1. Add or update a group in `.github/pr-issue-tasks.json`.
2. For canonical records, verify the task exists in its Story `tasks.md` and
   keep the Story key and path aligned.
3. Put exactly one matching group marker in the PR description.
4. After the workflow succeeds, copy each canonical issue key and URL from the
   PR summary into the corresponding task's External issue block and verify
   the mapping in both directions.
5. Keep ad hoc work in the registry unless it is promoted to product scope
   through BMAD change control.
