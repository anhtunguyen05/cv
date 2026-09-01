# GitHub Issues — Phase 2

Suggested labels: `phase-2`, `matching`, `backend`, `frontend`, `testing`.

## #P2-1 — Add JD revisions and match report schema

**Depends on:** Phase 1  
**Labels:** `phase-2`, `database`, `backend`

- [ ] Add `job_descriptions`, `job_description_revisions`, and `match_reports` migrations.
- [ ] Add models, relations, casts, factories, and indexes.
- [ ] Add `current_revision_id` and logical-deletion state to Job Descriptions.
- [ ] Add an immutable `job_description_analyses` record with stable
  `analysis_id`, `job_description_revision_id`, analysis JSON, and
  `analysis_rule_version`.
- [ ] Preserve raw JD and analysis separately from derived Match Report JSON.
- [ ] Store `job_description_id`, `job_description_revision_id`,
  `analysis_id`, `analysis_rule_version`, and `scoring_version` on derived
  records.
- [ ] Add relationship and ownership tests.

**Acceptance:** a report always identifies one owned CV Version, logical JD,
exact immutable JD revision, exact immutable `analysis_id` belonging to that
revision, analysis-rule version, and scoring version.

## #P2-2 — Implement deterministic JD analyzer

**Depends on:** `#P2-1`  
**Labels:** `phase-2`, `matching`, `backend`

- [ ] Add normalization and alias rules.
- [ ] Add controlled vocabulary and versioned weights.
- [ ] Extract skills, responsibilities, and soft-skill signals.
- [ ] Flatten CV evidence and classify matched/weak/missing skills.
- [ ] Persist each successful analysis with a stable immutable `analysis_id`
  linked to exactly one JD revision and `analysis_rule_version`.
- [ ] Add deterministic fixture tests.

**Acceptance:** identical inputs and scoring version produce identical output.

## #P2-3 — Implement JD lifecycle and matching API

**Depends on:** `#P2-1`, `#P2-2`  
**Labels:** `phase-2`, `backend`, `security`

- [ ] Add Form Requests, Policies, Resources, and v1 routes.
- [ ] Add create/read/update/delete/analyze JD actions.
- [ ] Make updates create immutable revisions and advance the current revision.
- [ ] Make deletion logical and block new analysis/comparisons for deleted JDs.
- [ ] Add create/read match report actions.
- [ ] Require the current JD revision to have successful analysis before matching.
- [ ] Require the submitted `analysis_id` to belong to the current JD revision.
- [ ] Persist the exact `analysis_id` consumed by each Match Report.
- [ ] Prevent new comparisons from selecting historical revisions.
- [ ] Test empty JD, mismatched ownership, invalid CV version, revision pinning,
  analysis identity/pinning, update preservation, logical deletion, and
  historical report access.

**Acceptance:** API exposes actionable `422`, `404`, and domain failure responses.
Historical Match Reports remain readable and identify their exact JD revision
after the parent JD is edited or logically deleted.

## #P2-4 — Build JD and match report UI

**Depends on:** `#P2-3`  
**Labels:** `phase-2`, `frontend`

- [ ] Add JD input, update/delete, revision, and draft/error states.
- [ ] Add report score and skill breakdown views.
- [ ] Link weak areas to CV sections.
- [ ] Add loading/retry behavior.
- [ ] Show when a historical report's parent JD has been logically deleted.

**Acceptance:** user can paste a JD and inspect a complete report in the browser.

## #P2-5 — Phase 2 checkpoint

**Depends on:** `#P2-2`, `#P2-3`, `#P2-4`  
**Labels:** `phase-2`, `testing`

- [ ] Run backend and frontend verification.
- [ ] Run one manual repeatability smoke test.
- [ ] Verify the chain `Job Description → Revision → Analysis → Match Report`.
- [ ] Record the analysis ID, scoring version, and checkpoint outcome.

**Acceptance:** all phase exit criteria pass.
