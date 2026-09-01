# Database

## 1. Current Storage State

The repository currently uses the default Laravel storage model:

- `users`
- `password_reset_tokens`
- `sessions`
- `cache`
- `cache_locks`
- `jobs`
- `job_batches`
- `failed_jobs`

There is also a SQLite database file in the API app for local development.

## 2. What This Means

The system does not yet persist CV-specific domain data. That is an important gap because the product depends on versioned structured documents.

## 3. Current Table Purpose

### Identity and Session

- `users`: application accounts
- `password_reset_tokens`: password recovery
- `sessions`: server-side session storage

### Cache

- `cache`, `cache_locks`: runtime cache and lock coordination

### Queue

- `jobs`, `job_batches`, `failed_jobs`: background work and failure tracking

## 4. Proposed Domain Tables

The README points to a more complete domain model:

- `cv_profiles`
- `cv_versions`
- `job_descriptions`
- `job_description_revisions`
- `job_description_analyses`
- `match_reports`
- `ai_interview_sessions`
- `ai_interview_messages`
- `cv_patches`
- `templates`
- `ai_tool_calls`

## 5. Suggested Relationships

```text
users
  -> cv_profiles
  -> job_descriptions
  -> ai_interview_sessions

cv_profiles
  -> cv_versions

cv_versions
  -> match_reports
  -> cv_patches

job_descriptions
  -> job_description_revisions

job_description_revisions
  -> job_description_analyses
  -> match_reports

match_reports
  -> exact job_description_revision and analysis
  -> ai_interview_sessions
  -> cv_patches
```

### Job Description Traceability

The canonical derived-data chain is:

```text
Job Description
  (job_description_id)
    -> immutable Job Description Revision
       (job_description_revision_id)
         -> immutable Analysis
            (analysis_id, analysis_rule_version)
              -> Match Report
                 (pins the exact revision and analysis)
```

Editing a Job Description creates a new immutable revision. Each persisted
Analysis has a stable immutable `analysis_id`, belongs to exactly one
`job_description_revision_id`, and records its `analysis_rule_version`. A Match
Report must store `job_description_id`, `job_description_revision_id`, and
`analysis_id`, so the exact stored analysis that produced the report can be
identified and reproduced. Historical revisions and Reports remain readable;
they are not replaced by later edits.

## 6. Schema Principles

### Use JSON for Variant Structures

Sections like projects, skills, and patch payloads change shape over time. JSON columns are a good fit when the app still needs flexibility.

### Version Everything User-Facing

The product should preserve previous CV states. That means `cv_versions` must be immutable once created, except for explicit state transitions.

### Log AI Tool Calls

The system should store tool call inputs, outputs, status, and timing for traceability.

### Separate Evidence from Interpretation

User-provided evidence, derived match scores, and AI-generated suggestions should not be collapsed into one field.

## 7. Missing Pieces

- no migration for CV domain entities
- no relation indexes for product entities
- no retention policy for AI logs
- no export metadata tables
