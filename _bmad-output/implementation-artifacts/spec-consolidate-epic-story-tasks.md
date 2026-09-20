---
title: 'Consolidate Epic Story Task Scopes'
type: 'refactor'
created: '2026-09-20'
status: 'done'
baseline_commit: '45056817c5a6efb27ee7c9833da2b4d66f89652d'
review_loop_iteration: 0
context:
  - '{project-root}/_bmad-output/planning-artifacts/epics.md'
  - '{project-root}/docs/story-execution.md'
---

<frozen-after-approval reason="human-owned intent - do not modify unless human renegotiates">

## Intent

**Problem:** The 29 stories across five epics currently have 6-10 top-level tasks each (217 tasks total). The task breakdowns are too fragmented to plan and own as meaningful implementation work.

**Approach:** Regroup each story into 2-3 larger, owner-sized tasks based on its existing outcomes and dependencies. Keep the canonical story scope and acceptance criteria intact, and update AC-to-task traceability to the new task IDs.

## Boundaries & Constraints

**Always:** Keep each task a bounded, reviewable outcome for one owner with an acyclic within-story dependency map. Retain every existing implementation, security, accessibility, failure, and verification outcome in the grouped tasks. Keep task status `todo`, owners and branch/worktree `unassigned`, and all existing epic/story lifecycle values unchanged.

**Ask First:** Pause if a story cannot fit into 2–3 tasks without combining unrelated owners or dropping a required outcome, or if preserving its scope requires a product decision.

**Never:** Change canonical story intent or ACs in `epics.md`; edit requirements, contracts, README, or epic rules; change application, infrastructure, validation, or lifecycle state; mark work complete; or remove an existing guard, prerequisite, or verification gate.

## Code Map

- `_bmad-output/planning-artifacts/epics.md` -- canonical story intent and ACs for Epic 1 (line 118), Epic 2 (372), Epic 3 (535), Epic 4 (618), and Epic 5 (761); source remains unchanged.
- `_bmad-output/planning-artifacts/epics/epic-*/stories/*/tasks.md` -- all 29 task boards; currently 217 `todo` tasks with 6-10 tasks per story. Task IDs appear in these files and in their paired traceability files only.
- `_bmad-output/planning-artifacts/epics/epic-*/stories/*/verification.md` -- AC-to-task mappings that must follow each story's regrouped task IDs; preserve other verification rules.
- `docs/story-execution.md` -- task ownership, outcome, dependency, verification, and lifecycle rules (`## Task contract`, line 167; `## Lifecycle synchronization`, line 225).
- `_bmad-output/implementation-artifacts/sprint-status.yaml` -- all 29 stories are `backlog`; inspect after editing and leave unchanged.
- Within `_bmad-output`, a read-only audit found no task-ID references outside `tasks.md` and `verification.md`. Preserve Epic 3's template/preview/print handoffs; Epic 4's evidence and patch approval gates; and Epic 5's conditional async-job, safe deletion, read-only evaluation, ADR-only, and evidence-verdict boundaries.

## Tasks & Acceptance

**Execution:**
- [ ] `_bmad-output/planning-artifacts/epics/epic-*/stories/*/tasks.md` -- regroup each story into 2-3 coherent tasks, usually three when backend, frontend, and integrated acceptance are distinct. Renumber tasks sequentially within the story; update `Depends on`, dependency/concurrency maps, and grouped task fields. Preserve `Status`, `Owner`, `Branch/worktree`, `Covers`, `Scope`, `Coordination`, `Blocked by`, `Outcome`, `Acceptance`, and `Verification`, as well as every existing outcome and prerequisite.
- [ ] `_bmad-output/planning-artifacts/epics/epic-*/stories/*/verification.md` -- remap every canonical AC to the new task IDs and retain all non-mapping verification content.
- [ ] Review all five epic packages for scope preservation, owner-sized tasks, dependency integrity, cross-story handoffs, and planning-only changes.

**Acceptance Criteria:**
- Given the 29 canonical stories, when the task boards are regrouped, then each has 2-3 top-level tasks and no original required outcome or verification gate is lost.
- Given task IDs change, when each verification matrix is reviewed, then every canonical AC maps to at least one existing task and no stale task ID remains.
- Given the existing task contract, when dependencies are checked, then each task has one owner-sized outcome, all within-story dependencies exist and are acyclic, and parallel work remains explicit where valid.
- Given the planning-only boundary, when the diff is reviewed, then story packages change only in `tasks.md` and `verification.md`, and `sprint-status.yaml` still shows all stories as `backlog`.

## Verification

**Commands:**
- `git diff --check` -- expected: no whitespace errors.

**Manual checks:**
- Run `bmad-review` against all five epic/story packages. Confirm 2-3 task counts, required task fields, complete AC traceability, valid dependency maps, preserved cross-story gates, and unchanged lifecycle state. Do not install or run application dependencies.

</frozen-after-approval>

## Spec Change Log

- 2026-09-20: Consolidated all 29 story task boards into 87 tasks, remapped 88 AC rows, and preserved lifecycle state and cross-story gates.

## Suggested Review Order

**Task grouping and handoffs**

- Start with the integrated three-task pattern and its backend-to-journey acceptance gate.
  [tasks.md:22](../planning-artifacts/epics/epic-1-trusted-cv/stories/1-1-register-an-account/tasks.md#L22)

- Follow deterministic matching through source pinning, persistence, API, and quality evidence.
  [tasks.md:23](../planning-artifacts/epics/epic-2-job-fit/stories/2-4-generate-a-match-report/tasks.md#L23)

- Review the Preview work packages around exact source, renderer, and accessibility acceptance.
  [tasks.md:23](../planning-artifacts/epics/epic-3-preview-export/stories/3-2-preview-a-saved-cv-version/tasks.md#L23)

- Trace provider adapter through validation and the human-approved Patch boundary.
  [tasks.md:20](../planning-artifacts/epics/epic-4-ai-revision/stories/4-3-generate-a-patch-proposal/tasks.md#L20)

**Conditional and operational gates**

- Provider recovery and conditional async failures keep separate acceptance evidence.
  [tasks.md:20](../planning-artifacts/epics/epic-5-operational-safety/stories/5-2-handle-provider-and-background-failures/tasks.md#L20)

- Deletion execution consumes a policy handoff while production remains separately gated.
  [tasks.md:33](../planning-artifacts/epics/epic-5-operational-safety/stories/5-3-retain-and-delete-user-data-safely/tasks.md#L33)

- Independent ADR review and rerun prove the decision without adding runtime scope.
  [tasks.md:33](../planning-artifacts/epics/epic-5-operational-safety/stories/5-6-decide-whether-multi-agent-orchestration-is-justified/tasks.md#L33)

- Readiness closure depends on source evidence, validator acceptance, and independent review.
  [tasks.md:33](../planning-artifacts/epics/epic-5-operational-safety/stories/5-7-verify-the-operational-safety-baseline/tasks.md#L33)

**Acceptance traceability**

- Each criterion has a final cross-layer evidence owner after contract and backend proof.
  [verification.md:15](../planning-artifacts/epics/epic-1-trusted-cv/stories/1-1-register-an-account/verification.md#L15)

- The two failure criteria map separately to provider recovery and the conditional async lifecycle.
  [verification.md:7](../planning-artifacts/epics/epic-5-operational-safety/stories/5-2-handle-provider-and-background-failures/verification.md#L7)
