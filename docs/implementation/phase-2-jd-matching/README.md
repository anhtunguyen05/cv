# Phase 2 — Job Description and Matching

Status: `planned`  
**Depends on:** Phase 1

## Outcome

A user can paste a JD, analyze it with deterministic rules, compare it to an
exact CV version, and inspect a reproducible match report.

## Data design

Add `job_descriptions` (`id`, `user_id`, `current_revision_id`, `deleted_at`),
`job_description_revisions` (`id`, `job_description_id`, immutable revision
number, raw text, title/company), `job_description_analyses` (immutable
`analysis_id`, `job_description_revision_id`, analysis JSON,
`analysis_rule_version`), and
`match_reports` (`cv_version_id`, `job_description_id`,
`job_description_revision_id`, score, matched/missing skills, weak sections,
recommendations, `analysis_id`, `analysis_rule_version`, `scoring_version`).
Keep raw input and derived output separate. Reports must reference the exact CV
Version, JD revision, and persisted analysis used.

Editing creates a new immutable revision and makes it current. Existing
analysis and Match Reports remain unchanged; the new revision must be analyzed
explicitly before a new Match Report can use it. Deleting a Job Description is
logical deletion: it is removed from active workflows, while its revisions,
analyses, and historical Match Reports remain accessible to its owner.
New comparisons use the current revision of a non-deleted Job Description;
older revisions are retained for historical analysis and Match Reports only.

## Processing design

Create a normalizer, controlled skill vocabulary, evidence flattener, and
scoring service under `apps/api/app/Services/Matching/`. Required skills weigh
more than nice-to-have skills. A skill listed without project/experience
evidence is `weak`, not fully matched. Keep weights and aliases versioned.

Each persisted analysis has a stable immutable `analysis_id` associated with
exactly one `job_description_revision_id` and `analysis_rule_version`. A Match
Report must store that exact `analysis_id`; it cannot point only to a revision
or reuse an analysis from another revision. The first implementation is
synchronous and provider-free. Queueing or LLM
enrichment is a later optimization, not a prerequisite for the report.

## Frontend and verification

Add JD input and update/delete states, revision-aware analysis status/retry,
report summary, skill breakdown, weak-area links, and recommendations. Test
normalization aliases, stable fixture scores, invalid input, ownership across
CV/JD/report resources, immutable revision creation, immutable analysis
identity, logical deletion, historical report access, and exact
JD-revision-to-analysis-to-report pinning.

```powershell
cd apps/api; php artisan test
cd ../web; npm run type-check; npm run build; npm run lint
```

## Checkpoint / exit criteria

- One JD can be saved and re-opened.
- One exact CV version can produce a repeatable report.
- The score is explainable from persisted fields, exact `analysis_id`, and
  `scoring_version`.
- The report does not require an AI key.

See [`github-issues.md`](github-issues.md).
