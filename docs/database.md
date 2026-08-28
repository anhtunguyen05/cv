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
  -> match_reports
  -> ai_interview_sessions
  -> cv_patches
```

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
