---
title: 'Plan Solo Delivery Sprints'
type: 'feature'
created: '2026-09-21'
status: 'done'
baseline_commit: '5438a98fa533646d396c097d7f036953d656bae8'
review_loop_iteration: 1
context:
  - _bmad-output/planning-artifacts/epics.md
  - _bmad-output/implementation-artifacts/sprint-status.yaml
  - docs/sprint-workflow.md
  - _bmad-output/implementation-artifacts/sprints/README.md
---

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

**Problem:** The current five draft charters group all stories by Epic as refinement batches. They do not give one developer small outcome-based delivery slices or a predictable way to map tasks to GitHub Issues or Jira.

**Approach:** Replace the five draft-only Epic batches with ten smaller, sequential draft sprint charters organized around story dependencies and user outcomes. Keep all stories in refinement, retain `backlog` lifecycle, and use permanent story/task keys as the cross-reference to any external issue tracker.

## Boundaries & Constraints

**Always:** Keep sprint membership only in charter frontmatter; keep Story lifecycle in `sprint-status.yaml` and task lifecycle in each Story's `tasks.md`. Keep dates unset until scheduling, use one-person capacity assumptions, make sprint IDs permanent, and permit new work to be added as a newly keyed task after dependency and scope review.

**Ask First:** Confirm this replacement of five draft refinement charters with ten delivery-oriented draft charters. Choose GitHub Issues or Jira only when actual external issue creation or synchronization is requested.

**Never:** Do not mark Stories ready-for-dev or committed while decisions and readiness reviews remain open. Do not change application code, duplicate task status into charters, fabricate external issue IDs, or push to `main`.

## I/O & Edge-Case Matrix

| Scenario | Input / State | Expected Output / Behavior | Error Handling |
|----------|--------------|---------------------------|----------------|
| Draft plan | All Story lifecycle values are `backlog`; existing charters are draft | Create outcome-sized draft charter memberships, with no committed keys | Preserve unresolved blockers in the charter |
| New task mid-sprint | A task is discovered within approved Story scope | Append a new permanent `TASK-<story>-NN` entry to that Story's `tasks.md`; do not renumber existing tasks | If independent or scope-expanding, return it to backlog for explicit planning |
| External issue mapping | GitHub or Jira is later selected | Put the canonical task key in the issue title/body and store its external key/link with the task record | Do not invent links or create issues before the platform is selected |

</frozen-after-approval>

## Code Map

- `_bmad-output/planning-artifacts/epics.md` — canonical Story keys, value slices, and dependencies.
- `_bmad-output/planning-artifacts/epics/epic-*/README.md` — epic outcomes, story sequencing, prerequisites, and coordination boundaries.
- `_bmad-output/planning-artifacts/epics/epic-*/stories/*/tasks.md` — permanent task IDs and current task boards; the source for task-level issue mapping.
- `_bmad-output/planning-artifacts/epics/epic-*/decisions.md` — open decisions that prevent commitment.
- `_bmad-output/implementation-artifacts/sprints/` — five existing draft refinement charters to replace or supersede after approval.
- `_bmad-output/implementation-artifacts/sprint-status.yaml` — current lifecycle; every Story is still `backlog` and this plan does not advance it.
- `docs/sprint-workflow.md` — charter lifecycle, capacity and membership rules, change control, and validation requirements.

## Tasks & Acceptance

**Execution:**
- [x] `_bmad-output/implementation-artifacts/sprints/` — replace the five Epic-wide drafts with ten small, sequential draft charters; retain old charters as cancelled with reason/owner and explicit successor IDs, and give new charters stable IDs.
- [x] Each new charter — group Stories by dependency: (1) 1.1–1.2 account access; (2) 1.3 profile core; (3) 1.4–1.7 CV sections; (4) 1.8 immutable Version; (5) 2.1–2.2 Job Description lifecycle; (6) 2.3–2.5 deterministic analysis and Match Report; (7) 3.1–3.3 Preview/Export; (8) 4.1–4.3 evidence/proposal; (9) 4.4–4.6 patch decision/apply; (10) 5.1–5.7 operational safety.
- [x] Each charter — record one measurable outcome, one-developer capacity assumption, and only `refinement_stories`; leave dates unset and `committed_stories` empty until readiness is approved. Copy every listed Story planning blocker, annotate which Story it applies to, and do not add blockers absent from those packages.
- [x] Each charter — define a measurable done signal: 100% canonical AC-to-task/verification coverage, no unreviewed BMAD findings unless recorded as named decisions, and every unresolved prerequisite retained as a blocker. In Sprint 15, identify Story 5.5 as the MVP baseline and Stories 5.1–5.4/5.6–5.7 as post-MVP refinement only.
- [x] Sprints 13–15 — distinguish contract refinement from implementation order: refine Epic 4 provider/Patch contracts before Epic 5 controls, and defer production provider activation until required Epic 5 controls are complete.
- [x] `_bmad-output/implementation-artifacts/sprints/README.md` — document task-key mapping to GitHub/Jira, reverse-link verification, and change control without making the external tool another canonical status source.
- [x] `_bmad-output/implementation-artifacts/sprint-status.yaml` — leave unchanged unless approved Stories pass the readiness gate and the user explicitly commits them.

**Acceptance Criteria:**
- Given a Story key, when I look up its charter, then it occurs in at most one non-closed, non-cancelled charter.
- Given a task in a Story package, when I create its GitHub/Jira issue, then its permanent `TASK-*` key, canonical Story key, platform issue key, and direct URL resolve bidirectionally between that exact task and issue without copying task status into the charter; verify both directions by opening the recorded URL and matching both keys.
- Given an open decision or dependency, when I review the charter, then the Story remains a refinement candidate and no readiness or delivery commitment is implied.
- Given a new independent task during a sprint, when scope is reviewed, then it can be appended with a new key without renumbering existing tasks or silently changing sprint membership.
- Given no dates or velocity baseline, when these charters are created, then they remain draft with unset dates and no fabricated time estimates.

## Spec Change Log

- 2026-09-21, review iteration 1: The first review found missing and over-broad Story blockers, vague charter completion signals, unclear external-issue reverse checks, and an implementation-order loop between provider work and operational controls. Added explicit per-Story blocker alignment, measurable done signals, bidirectional issue-link verification, and separate contract-planning/implementation dependencies while keeping the approved ten sprint groupings and backlog-only lifecycle. This avoids charters implying readiness where a source Story still has a blocker, or production provider work starting before its controls. KEEP: ten outcome-based slices, stable task keys, cancelled-charter history, no fabricated dates/capacity, and no tracker lifecycle advancement.

## Design Notes

The ten slices keep auth, Profile, content, and immutable Version work in dependency order, then place JD lifecycle before matching, and separate proposal creation from patch approval. This is a forecast, not a promise of calendar duration: one-person throughput is not yet measured. A sprint can carry fewer stories after refinement, and an oversized story should be split into independently accepted tasks in its permanent task board rather than moved between charters without recording the change.

## Verification

**Manual checks:**
- Parse every charter frontmatter and confirm unique IDs, `status: draft`, dates unset, `committed_stories: []`, and valid Story keys.
- Compare charter membership keys against the canonical Story keys in `epics.md`; report missing, duplicate, and unknown keys, and confirm every canonical Story has exactly one refinement home.
- Review every charter blocker against each included Story's planning blockers; check the Story-to-blocker mapping and ensure no extra or missing decision IDs.
- Review measurable done signals, MVP/post-MVP labels, capacities, implementation prerequisites, and dependency order against Epic packages and the unchanged lifecycle tracker.
- Run the repository's `bmad-review` and the `bmad-sprint-planning` dry-run/validation flow after approved charter edits; reconcile warnings before writing tracker state.

**Recorded results (2026-09-21):**
- Static charter audit: 15 unique sprint IDs; five cancelled historical charters and ten draft successors; all 29 canonical Stories assigned exactly once to a non-cancelled charter; no committed Stories and no dates; every charter Story key resolves to `epics.md`; tracker lifecycle remains `backlog`.
- `bmad-sprint-planning generate --dry-run`: `in_sync: true`, 29 Stories, zero warnings, new entries, changed entries, upgraded statuses, orphans, and illegal states.
- `bmad-sprint-planning validate`: `valid: true`, no problems or legacy mappings.
- A real `generate` write was attempted after the clean preview but could not serialize through the temporary PyYAML compatibility shim (`cannot represent an object`); the script restored the original tracker. `sprint-status.yaml` remains unchanged. The environment lacks `ruamel.yaml`, and application dependencies were not installed for this planning-only change.
- `git diff --check`: passed. No application tests were run because this change only updates planning documents.

## Suggested Review Order

**Sprint shape and membership**

- Start with the first outcome slice and its explicit blockers.
  [`sprint-06-account-access.md:22`](sprints/sprint-06-account-access.md#L22)
- Check the shared Profile checkpoint before the section Stories.
  [`sprint-07-profile-core.md:35`](sprints/sprint-07-profile-core.md#L35)
- Review task-level Version dependencies across the CV content boundary.
  [`sprint-08-cv-sections.md:41`](sprints/sprint-08-cv-sections.md#L41)
- Confirm the immutable Version contract is sequenced before consumers.
  [`sprint-09-immutable-version.md:35`](sprints/sprint-09-immutable-version.md#L35)
- Follow JD ownership into the analysis and Match Report checkpoint.
  [`sprint-10-job-description-lifecycle.md:40`](sprints/sprint-10-job-description-lifecycle.md#L40)
- Review matching and report dependencies before Preview and Export.
  [`sprint-11-analysis-and-match-report.md:39`](sprints/sprint-11-analysis-and-match-report.md#L39)
- Check the user-facing Preview and Export outcome slice.
  [`sprint-12-preview-export.md:23`](sprints/sprint-12-preview-export.md#L23)

**AI contract and operational boundaries**

- Trace accepted provider and Patch contracts into later control work.
  [`sprint-13-evidence-and-proposal-contracts.md:39`](sprints/sprint-13-evidence-and-proposal-contracts.md#L39)
- Check review, apply, and regenerate dependencies against those contracts.
  [`sprint-14-patch-decision-and-apply.md:39`](sprints/sprint-14-patch-decision-and-apply.md#L39)
- Verify MVP separation and the future readiness gate for Story 5.7.
  [`sprint-15-operational-safety.md:49`](sprints/sprint-15-operational-safety.md#L49)

**Tracking and historical transition**

- Review permanent task keys and bidirectional GitHub/Jira mapping rules.
  [`README.md:22`](sprints/README.md#L22)
- Confirm the superseded Epic-wide charter remains cancelled with a successor.
  [`sprint-01-trusted-cv-refinement.md:4`](sprints/sprint-01-trusted-cv-refinement.md#L4)
