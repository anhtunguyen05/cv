# Implementation Plan

This document converts the product vision in `README.md` and the design notes in
`docs/` into an executable implementation plan. It is intentionally more
concrete than the architecture documents: every phase has a scope boundary,
expected code locations, data/API work, verification steps, and a definition
of done.

## 1. Implementation Baseline

### 1.1 What exists today

The repository is an application scaffold, not an MVP yet:

| Area | Current implementation | Evidence |
| --- | --- | --- |
| Web | Vue 3 + Vite + Pinia + Vue Router starter screens | `apps/web/src/` |
| API | Laravel 13 application skeleton | `apps/api/composer.json` |
| API health | `GET /api/health`; Laravel framework health is `GET /up` | `apps/api/routes/api.php`, `apps/api/bootstrap/app.php` |
| Worker | Small stdlib Python HTTP service with `/health` and `/api/health` | `apps/worker/app/` |
| Persistence | Default Laravel tables/migrations; local SQLite is available, Docker Compose provisions MySQL and Redis | `apps/api/database/migrations/`, `apps/api/docker-compose.yml` |
| Product domain | No CV, JD, matching, interview, patch, template, or export modules | No domain migrations/models/controllers exist yet |

The first implementation tasks must preserve this boundary. Existing starter
routes and health checks are regression checks, not proof that product features
are implemented.

### 1.2 Working assumptions for the MVP

These assumptions make the phases executable without prematurely committing to
the full target architecture:

- Laravel is the source of truth for product state and trusted transitions.
- Vue is the only user-facing client in the MVP.
- A CV is stored as validated structured JSON; raw text is retained only where
  it is an input or audit artifact.
- Matching starts with deterministic rules. An LLM may explain or enrich a
  result later, but it must not be required for the first vertical slice.
- AI suggestions are persisted as patches and require explicit user approval.
- The first export can use browser print/HTML-to-PDF. A separate worker is
  optional until export requirements justify it.
- Use Laravel database queues before introducing a dedicated queue service in
  application code. Redis is available in Docker Compose but is not a required
  MVP dependency.

### 1.3 Definition of done for every phase

A phase is complete only when:

1. Its schema and business rules are represented in code.
2. Its API and UI behavior are covered by automated tests at the appropriate
   boundary.
3. Validation and authorization are enforced server-side.
4. The feature can be demonstrated from a clean local setup using the commands
   in the repository documentation.
5. The relevant documentation is updated to distinguish implemented behavior
   from future work.

## 2. Cross-cutting Conventions

### 2.1 Backend module layout

Keep Laravel modules conventional and easy to discover:

```text
apps/api/app/
├── Http/Controllers/Api/V1/
├── Http/Requests/
├── Http/Resources/
├── Models/
├── Policies/
├── Services/
├── Jobs/
└── Support/
apps/api/database/
├── factories/
├── migrations/
└── seeders/
apps/api/tests/
├── Feature/Api/V1/
└── Unit/
```

Use Form Requests for input validation, Policies for ownership checks, API
Resources for stable response shapes, and Services for multi-record state
transitions. Controllers should coordinate these components rather than hold
matching, patch, or AI logic.

### 2.2 API conventions

- Prefix product routes with `/api/v1`.
- Use authenticated user ownership for every user-owned resource.
- Return `201` for creation, `200` for reads/actions, `204` for successful
  deletion where no body is needed, `422` for validation, and `404` when an
  owned resource is not visible to the caller.
- Keep the existing error shape:

  ```json
  {
    "message": "Validation failed",
    "errors": {"field": ["The field is invalid."]}
  }
  ```

- Use explicit resource IDs and action endpoints for state transitions such as
  accepting a patch or starting an export.
- Never allow an AI provider response to write directly to an Eloquent model.

### 2.3 Structured CV contract

Start with a versioned application contract. The initial `data` object should
support:

```json
{
  "personal_info": {"full_name": "", "email": "", "phone": "", "location": ""},
  "summary": "",
  "skills": {"frontend": [], "backend": [], "tools": [], "other": []},
  "education": [],
  "experience": [],
  "projects": [],
  "certificates": [],
  "languages": [],
  "activities": []
}
```

The JSON contract must define required fields, maximum lengths, allowed patch
paths, and item identifiers. Store a `schema_version` beside the JSON so a
future migration can transform old versions deliberately.

## 3. Phase 0 — Foundation and Vertical-Slice Preparation

### Goal

Make the current scaffold ready for product work without adding speculative
services. Establish environment configuration, API versioning, authentication
boundaries, shared types, and test conventions.

### Implementation tasks

1. Confirm local and Docker development commands for `apps/web`, `apps/api`,
   and `apps/worker`. Keep frontend Vite optional when working backend-only.
2. Add an API v1 route group and a minimal authenticated identity endpoint.
3. Add Sanctum only when the chosen browser authentication flow is documented;
   do not mix cookie and token behavior implicitly.
4. Add a shared API client in `apps/web/src/` with a single configurable base
   URL from `VITE_API_BASE_URL`.
5. Replace starter navigation with a small application shell: login state,
   authenticated layout, error state, and loading state.
6. Add API Feature tests and frontend type-check/build checks to the normal
   verification path.

### Expected code locations

- `apps/api/routes/api.php`
- `apps/api/bootstrap/app.php`
- `apps/api/app/Http/Controllers/Api/V1/`
- `apps/api/app/Http/Requests/`
- `apps/api/tests/Feature/Api/V1/`
- `apps/web/src/router/`, `apps/web/src/stores/`, `apps/web/src/lib/`
- `apps/web/.env.example`

### Verification

```powershell
cd apps/worker
python -m unittest discover -s tests

cd ../web
npm run type-check
npm run build
npm run lint

cd ../api
php artisan test
```

### Exit criteria

- Existing health endpoints still pass.
- An unauthenticated request cannot access a protected v1 endpoint.
- A frontend request can display an API error without exposing internal stack
  traces.
- No product feature depends on a provider key or background worker yet.

## 4. Phase 1 — Auth, CV Profile, and Versioned CV Data

### Goal

Deliver the first useful vertical slice: a user can create, edit, view, and
version a structured CV profile.

### Data model

Add these tables in dependency order:

1. `cv_profiles`
   - `id`, `user_id`, `title`, `base_data` JSON, `schema_version`, timestamps
2. `cv_versions`
   - `id`, `cv_profile_id`, `version_name`, `data` JSON, `schema_version`,
     `source`, `created_by`, timestamps
3. Optional `cv_profile_active_versions`
   - only if active-version lookup cannot remain a simple relation/column

Use foreign keys and indexes on `user_id` and `cv_profile_id`. A version is an
immutable snapshot. The CV Profile is mutable; creating a named CV Version
creates an immutable snapshot, and Profile edits never mutate existing
Versions.

### API surface

```text
GET    /api/v1/cv-profiles
POST   /api/v1/cv-profiles
GET    /api/v1/cv-profiles/{cvProfile}
PATCH  /api/v1/cv-profiles/{cvProfile}
DELETE /api/v1/cv-profiles/{cvProfile}
GET    /api/v1/cv-profiles/{cvProfile}/versions
POST   /api/v1/cv-profiles/{cvProfile}/versions
GET    /api/v1/cv-versions/{cvVersion}
```

Validate nested JSON in a Form Request. The server must reject unknown or
oversized values before persistence. Use Policies so a user cannot read or
write another user's profile by changing an ID.

### Frontend work

- Add CV profile list and editor views.
- Model the CV contract in TypeScript.
- Keep form state local until save; show server validation per field.
- Add a version list and read-only version view.
- Do not build template editing into the data model yet; the editor produces
  structured data and the renderer consumes it.

### Tests

- Create/read/update/delete ownership tests.
- Nested CV validation tests, including empty optional sections and invalid
  item IDs.
- Version snapshot test proving an older version does not change after a later
  save.
- Frontend type-check and a focused component test for validation display.

### Exit criteria

A new user can save a CV, refresh the browser, view the same structured data,
and create a second version while the first remains unchanged.

## 5. Phase 2 — Job Description Intake and Deterministic Matching

### Goal

Turn a CV version and pasted JD into an inspectable match report without
requiring an LLM.

### Data model

Add:

- `job_descriptions`: `id`, `user_id`, `current_revision_id`, `deleted_at`,
  timestamps
- `job_description_revisions`: `id`, `job_description_id`, immutable revision
  number, company/role metadata, `raw_text`, timestamps
- `job_description_analyses`: immutable `analysis_id`,
  `job_description_revision_id`, `analysis_rule_version`, analysis JSON,
  timestamps
- `match_reports`: `id`, `cv_version_id`, `job_description_id`,
  `job_description_revision_id`, `score`, `matched_skills`, `missing_skills`,
  `weak_sections`, `recommendations`, `analysis_id`, `analysis_rule_version`,
  `scoring_version`, timestamps

Keep raw inputs and derived output separate. Editing creates an immutable Job
Description revision and never mutates a revision used by analysis or a Match
Report. Each persisted analysis has a stable immutable `analysis_id` associated
with exactly one `job_description_revision_id` and `analysis_rule_version`. A
report records the exact CV Version, logical Job Description,
`job_description_revision_id`, `analysis_id`, `analysis_rule_version`, and
`scoring_version`.
Deleting a Job Description is logical: it is hidden from active lists and
cannot be edited, analyzed, or used for a new comparison, while its revisions,
analyses, and historical Match Reports remain accessible to its owner.
New comparisons select the current revision of a non-deleted Job Description;
older revisions are retained for historical analysis and Match Reports only.

### Processing design

Implement a deterministic analyzer service first:

1. Normalize case, whitespace, punctuation, and common aliases.
2. Extract a controlled skill vocabulary from the JD.
3. Extract responsibilities and soft-skill phrases using explicit rules.
4. Flatten CV evidence from skills, projects, education, and experience.
5. Score required skills more heavily than nice-to-have skills.
6. Mark a skill as `weak` when it appears only in a skill list without evidence
   in a project or experience item.
7. Persist the immutable analysis record and complete report, including the
   exact `analysis_id` and source inputs used for scoring.

The vocabulary and weights belong in configuration or a versioned service, not
in a controller.

### API surface

```text
GET    /api/v1/job-descriptions
POST   /api/v1/job-descriptions
GET    /api/v1/job-descriptions/{jobDescription}
PATCH  /api/v1/job-descriptions/{jobDescription}
DELETE /api/v1/job-descriptions/{jobDescription}
POST   /api/v1/job-descriptions/{jobDescription}/analyze
POST   /api/v1/match-reports
GET    /api/v1/match-reports/{matchReport}
```

`POST /match-reports` must identify the exact CV Version and the current,
successfully analyzed Job Description revision and its `analysis_id`. The
`analysis_id` must belong to that revision and its `analysis_rule_version`. A
revision without successful analysis is rejected with an actionable state.
Re-running a report creates a new historical result or
explicitly replaces a draft; it must not overwrite history. Reports generated
from logically deleted Job Descriptions remain readable to their owner.

### Frontend work

- JD input form with character limits and draft persistence.
- Analysis status and retry state.
- Match report with score, matched/missing skills, weak evidence, and
  recommendations.
- Links from each weak area to the relevant CV section.

### Tests

- Normalization and alias unit tests.
- Stable scoring fixture tests.
- Authorization tests across CV/JD/report combinations.
- API tests for invalid empty JDs and mismatched resource ownership.

### Exit criteria

Given a saved CV version and JD, the user can generate and inspect a repeatable
match report. The report explains its score using stored, inspectable fields.

## 6. Phase 3 — Evidence Interview and Reviewable CV Patches (post-MVP)

### Goal

Collect missing evidence and produce safe, reviewable proposals without
allowing AI output to mutate CV data automatically.

### Data model

Add:

- `ai_interview_sessions`: `id`, `user_id`, `cv_version_id`,
  `job_description_id`, `match_report_id`, `status`, timestamps
- `ai_interview_messages`: `id`, `session_id`, `role`, `content`, `metadata`,
  timestamps
- `cv_patches`: `id`, `cv_version_id`, `job_description_id`, `status`,
  `patch`, `reason`, `evidence_sources`, `validated_at`, `applied_at`,
  timestamps

Recommended patch statuses are `pending`, `accepted`, `rejected`, `applied`,
and `invalid`. State transitions must be enforced by a service and recorded
with timestamps.

### Patch contract

```json
{
  "section": "projects",
  "item_id": "edura",
  "field": "bullets",
  "operation": "replace",
  "old_value": ["Built an online tutoring platform."],
  "new_value": ["Integrated REST APIs for the booking flow."],
  "reason": "Based on the user's interview answer.",
  "evidence_sources": ["user_follow_up_answer"]
}
```

Validation must check:

- exact section/field allowlist
- item existence in the selected CV version
- `old_value` matches the current snapshot for replace/remove operations
- new content length and type
- at least one evidence source
- no claims that cannot be traced to existing CV data or a user answer

### Processing boundary

Create interfaces such as `InterviewQuestionGenerator` and
`CvPatchGenerator`. A fake implementation should be usable in tests. The
provider adapter returns a DTO; a validation service converts the DTO into a
persisted patch proposal. The adapter must not receive a model instance with
write access and must not call `save()`.

### API surface

```text
POST   /api/v1/ai/interviews
GET    /api/v1/ai/interviews/{session}
POST   /api/v1/ai/interviews/{session}/messages
POST   /api/v1/ai/interviews/{session}/generate-patches
GET    /api/v1/cv-versions/{cvVersion}/patches
POST   /api/v1/cv-patches/{patch}/accept
POST   /api/v1/cv-patches/{patch}/reject
POST   /api/v1/cv-patches/{patch}/regenerate
```

### Apply transaction

When a user accepts a patch:

1. Authorize the user against the patch's CV profile.
2. Lock or re-read the source version.
3. Revalidate the patch against the current snapshot.
4. Apply it to a copied JSON document.
5. Validate the resulting document.
6. Create a new immutable `cv_versions` row.
7. Mark the patch `applied` and set `applied_at`.
8. Commit as one transaction; leave the patch pending if any step fails.

This prevents a stale proposal from silently overwriting newer user edits.

### Frontend work

- Interview conversation view with explicit question/answer states.
- Patch review cards showing old value, new value, reason, and evidence.
- Accept, reject, edit, and regenerate actions with conflict/error states.
- Preview the proposed version before applying it.

### Tests

- Patch schema and allowlist tests.
- Tests rejecting fabricated/missing evidence metadata.
- Stale `old_value` conflict test.
- Transaction test proving acceptance creates a new version.
- Authorization tests for interview and patch resources.
- Provider adapter tests using a fake provider; no real API key in CI.

### Exit criteria

The user can answer a question, inspect a patch, reject it, or approve it to
create a new version. No AI response can bypass validation or approval.

## 7. Phase 4 — Template Preview, Export, and Product Polish (MVP)

### Goal

Render an approved CV version and provide a reliable downloadable artifact.

### Data model

Add:

- `templates`: `id`, `name`, `type`, `config`, `preview_image`, `is_active`,
  timestamps
- Optional post-MVP `export_jobs`: `id`, `user_id`, `cv_version_id`,
  `template_id`, `status`, `format`, `storage_path`, `error_message`,
  `started_at`, `completed_at`, timestamps. This table is not required for MVP
  Preview/Export.

Do not store an editable PDF as the source of truth. Render from the immutable
CV version and template configuration every time.

MVP Preview/Export uses browser print/HTML only. It does not require background
export jobs, queue infrastructure, or worker-side Export persistence.

### Implementation order

1. Build one responsive HTML/CSS template in Vue.
2. Render the same data in a print-friendly route.
3. Add browser print/download as the first export path.
4. Post-MVP only: add a queue-backed export job when generation is slow or
   needs a server-side artifact.
5. Add storage cleanup and retention rules before production deployment.

### API surface

```text
GET    /api/v1/templates
GET    /api/v1/templates/{template}
```

The MVP has no Export creation or status API. The following endpoints are
post-MVP only when asynchronous or server-side Export is justified:

```text
POST   /api/v1/cv-versions/{cvVersion}/exports
GET    /api/v1/exports/{exportJob}
```

They require the optional `export_jobs` persistence and are not prerequisites
for browser print/HTML Export, background jobs, queue infrastructure, or
worker/server-side Export persistence in the MVP.

### Tests and acceptance

- Snapshot/render tests for required CV sections.
- Authorization test preventing access to another user's export.
- Export failure is visible and retryable.
- The downloaded artifact contains the selected version, not unsaved editor
  state.
- A user can preview and export a CV without an AI provider configured.

## 8. Phase 5 — Observability, Safety, and Advanced AI

### Goal

Add operational controls only after the core workflow is stable.

### Scope

- `ai_tool_calls` with tool name, sanitized input/output, status, duration,
  provider/model, and retention metadata.
- Prompt template versioning.
- Provider timeout, retry, rate-limit, and circuit-breaker behavior.
- Redaction of tokens and unnecessary personal data from logs.
- Review/ATS checks as deterministic validators plus optional AI explanation.
- Background jobs for slow analysis and export tasks.
- Metrics for request latency, failed jobs, patch rejection rate, and provider
  errors.
- Multi-agent decomposition only when a single orchestrator has measurable
  limits; preserve the same tool and approval boundaries.

### Safety gates

- JSON Schema validation before persistence.
- Evidence provenance on every generated claim.
- Human approval for every patch.
- Provider keys only in environment/secret storage.
- Structured logs without raw CV/JD content by default.
- Retention and deletion behavior documented for AI messages and tool calls.

### Exit criteria

Operators can determine what happened in a failed analysis or export without
reading sensitive content, and users can delete or revoke their stored data
according to the documented retention policy.

## 9. Cross-Phase API and Data Rules

### Ownership graph

```text
User
├── CV profiles
│   └── immutable CV versions
├── Job descriptions
└── Interview sessions
    └── messages

CV version + Job description
└── match report
    └── interview session
        └── CV patch proposals
            └── approved new CV version
```

Every child lookup must resolve through an ownership-aware relation. Avoid
accepting a user ID from the client as an authorization input.

### Idempotency and concurrency

- Analysis and export commands should accept an idempotency key once they are
  asynchronous.
- Patch application must be safe to retry after a network timeout; use a
  transition check and a transaction.
- A report or export must reference the exact CV version and scoring/template
  version used.

### Error handling

Keep domain errors actionable: invalid state transition, stale patch, provider
unavailable, export failed, and validation failed should be distinguishable in
the API while avoiding internal stack traces.

## 10. Suggested Delivery Sequence

**execution_order:** `0 → 1 → 2 → 4 → 3 → 5`

The phase folder numbers are retained for continuity and do not represent
delivery order. Phase 4 completes the MVP Preview/Export path before Phase 3
post-MVP AI Revision work.

Implement in small vertical slices:

1. Phase 0: shell, auth boundary, test baseline.
2. Phase 1: create/edit/read one structured CV and one immutable version.
3. Phase 2: paste one JD and produce one deterministic report.
4. Phase 4: preview and export the resulting saved version (MVP completion).
5. Phase 3: answer one evidence question and review/apply one patch (post-MVP).
6. Phase 5: harden, observe, and optimize.

At the end of each slice, demonstrate the user path end to end before adding
the next module. If a phase grows beyond its exit criteria, split the work;
do not hide unfinished behavior behind a broad endpoint or a feature flag that
has no documented owner.

## 11. Implementation Checklist

### Before coding a phase

- Confirm the phase exit criteria and unresolved decisions.
- Inspect current migrations/routes/components before adding files.
- Define the request/response examples and ownership rules.
- Decide whether the work is synchronous or queued.

### During coding

- Add migration, model relation, policy, request, resource, service, route,
  test, and UI behavior together where applicable.
- Keep DTOs/contracts explicit at AI and export boundaries.
- Add negative tests before integrating external providers.
- Update the relevant design document when an assumption becomes a decision.

### Before marking complete

- Run backend tests from `apps/api`.
- Run worker tests when worker code changed.
- Run frontend type-check, build, and lint when web code changed.
- Run a manual request against the changed endpoint.
- Verify the documentation says what is implemented, not only what is planned.

## 12. Open Decisions to Resolve at Phase Boundaries

These decisions should be made when the indicated phase begins, not left as
implicit implementation details:

| Decision | Resolve by | Default if no stronger requirement appears |
| --- | --- | --- |
| Browser auth mechanism | Phase 0 | Laravel Sanctum cookie auth |
| Production database | Phase 0/1 | MySQL, with SQLite for fast local tests |
| CV edit/version semantics | Phase 1 | Draft profile plus immutable named versions |
| JD analysis provider | Phase 2 | Deterministic rules first |
| Initial AI provider/model | Phase 3 | Adapter interface plus fake provider in tests |
| Export mechanism | Phase 4 | Browser print/HTML for MVP, then queued server export if needed |
| AI log retention | Phase 5 | Minimal metadata, redacted payloads, documented deletion |

The existing `docs/architecture.md`, `docs/database.md`, `docs/api.md`, and
`docs/ai-workflow.md` remain the conceptual references. This document is the
implementation sequencing reference and should be updated when code changes
the agreed design.
