# Phase 3 — Evidence Interview and AI Patches

Status: `planned`  
**Depends on:** Phase 2

## Outcome

The system asks targeted evidence questions and stores AI-generated patch
proposals that users can review, reject, edit, or approve. Approval creates a
new CV version; no provider response writes directly to storage.

## Data and contracts

Add `ai_interview_sessions`, `ai_interview_messages`, and `cv_patches` as
described in the parent plan. Use patch states `pending`, `accepted`,
`rejected`, `applied`, and `invalid`. Store evidence sources, validation time,
and application time.

Create provider-neutral interfaces such as `InterviewQuestionGenerator` and
`CvPatchGenerator`. Adapters return DTOs. A patch validator enforces section and
field allowlists, item existence, stale `old_value` checks, content limits, and
evidence provenance.

## Approval transaction

Authorize the patch, re-read/lock its source version, validate again, apply to a
copy, validate the result, create a new immutable version, mark the patch
applied, and commit transactionally. A stale patch must remain unapplied and
return a conflict response.

## Frontend and verification

Build interview conversation and patch review cards with old/new values,
reason, evidence, and explicit actions. Use a fake provider in tests; no real
provider key is required for CI.

```powershell
cd apps/api; php artisan test
cd ../web; npm run type-check; npm run build; npm run lint
```

## Checkpoint / exit criteria

- A user answer can produce a stored proposal.
- Unsupported fields, missing evidence, and stale values are rejected.
- Accepting a patch creates a new version atomically.
- Rejecting a patch leaves the source version unchanged.

See [`github-issues.md`](github-issues.md).
