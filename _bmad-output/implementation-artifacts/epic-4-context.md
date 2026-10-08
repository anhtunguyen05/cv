# Epic 4 Context: Improve a CV with Evidence-Based AI Revision

<!-- Compiled from planning artifacts. Edit freely. Regenerate with compile-epic-context if planning docs change. -->

## Goal

Allow an authenticated User to respond to targeted gaps from an owned,
source-pinned Match Report, preserve attributable User Evidence, review a
bounded AI Patch proposal, and explicitly approve a validated change into one
new immutable CV Version. The source Version, Match Report, Evidence history,
and prior Patch decisions remain unchanged.

## Stories

- Story 4.1: Start an Evidence interview
- Story 4.2: Answer Evidence questions
- Story 4.3: Generate a Patch proposal
- Story 4.4: Review, edit, or reject a Patch
- Story 4.5: Approve a Patch into a new CV Version
- Story 4.6: Regenerate a rejected or invalid Patch

## Requirements & Constraints

- Laravel owns authentication, ownership, validation, persistence, state
  transitions, idempotency, and trusted provenance; Vue owns presentation only.
- Every action resolves the complete owner graph: User, Match Report, CV
  Version, JD revision, Analysis, Interview, Evidence, and Patch.
- Match Report areas are its stored `missing_skills` and `weak_evidence`
  signals. An Interview snapshots at most five areas in stored report order and
  uses one deterministic template question per area (`question_set_version=1.0`).
- Evidence accepts exactly one User `answer` or `cannot_provide` outcome.
  Submitted original text is immutable; a correction starts a new Interview.
- A Patch is one operation on `summary`, an existing Experience/Project
  highlight, or a new highlight on an existing Experience/Project item. It must
  carry target identity, exact old-value preconditions where applicable, and
  positive supporting Evidence IDs.
- Provider output is untrusted and cannot write persistence, approve a Patch,
  or create a Version. Invalid or ungrounded output creates no pending Patch.
- Apply is one PostgreSQL transaction: lock/revalidate, transform and validate
  the complete snapshot, insert one immutable Version, and mark the Patch
  applied. Failure commits neither trusted result.

## Technical Decisions

- Implementation uses a deterministic `PatchProposalProvider` fake only. No
  production provider, real User traffic, raw prompt/output persistence,
  queue, worker, or automatic retry is in scope.
- Generation is synchronous with a 15-second deadline, one active attempt per
  Interview, and five attempts per User per minute. Replay uses
  `Idempotency-Key`; Patch decisions also use `If-Match` revision.
- Planned routes are under `/api/v1`: Interview start/read/answer/generate and
  Patch read/edit/reject/approve/regenerate actions. Exact envelopes and error
  fixtures belong to the Story contracts.
- Production provider selection, legal/privacy/retention approval, operational
  monitoring, quality rollout thresholds, and kill-switch controls remain Epic
  5 release gates.

## UX & Interaction Patterns

- The journey begins from an eligible Match Report. No-area state explains that
  no Interview is needed. Loading, submit, expiry, stale, retryable failure,
  terminal validation failure, and lost-response states are explicit.
- Review clearly separates current CV source, User Evidence, provider proposal,
  User edits, and applied result. Approval is never preselected or automatic.
- Existing hard-coded `/ai/interview/:id` and `/cv/:id/patches` pages are
  placeholders and must be replaced with server-validated state. Keyboard
  navigation, focus restoration, screen-reader announcements, and color-safe
  diff/status semantics are required.

## Cross-Story Dependencies

- Epic 1's immutable CV Version v1 contract is the source/apply boundary and
  must not be reinterpreted by Epic 4.
- Epic 2's accepted immutable Match Report consumer contract is required before
  Interview eligibility and downstream generation can begin; current Epic 2
  review acceptance remains the implementation prerequisite.
- Stories sequence as 4.1 Interview, 4.2 Evidence, 4.3 generation, 4.4
  review/decision, 4.5 atomic apply, with 4.6 reusing the provider/Patch path
  from an eligible rejected or invalid predecessor.
- Shared Interview, provider, Patch, apply, and test fixtures each have one
  coordination owner before implementation tasks enter `doing`.
