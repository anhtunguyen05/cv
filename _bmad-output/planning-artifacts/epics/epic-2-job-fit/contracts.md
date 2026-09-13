# Epic 2 Shared Contracts

These are proposed planning agreements. Global envelope, identifiers,
timestamps, pagination, and common failure behavior come from
`docs/contracts/common/http.md`. Open values are owned by `decisions.md`.

## E2-CONTRACT-JD-001 — Logical Job Description

```json
{
  "id": "ULID",
  "company": "string or null",
  "role": "string or null",
  "current_revision": {},
  "deleted_at": "UTC ISO-8601 or null",
  "created_at": "UTC ISO-8601",
  "updated_at": "UTC ISO-8601"
}
```

- Active list/detail responses expose the current revision through the approved
  representation without accepting an owner ID.
- Top-level `company` and `role` are read projections of `current_revision`,
  not independently mutable root fields.
- Historical context may expose deleted state only to the owner and only where
  an existing Analysis or Match Report references it.
- Exact field limits, list filters, pagination, include strategy, and deletion
  representation remain open.

## E2-CONTRACT-JD-REVISION-001 — Immutable revision

```json
{
  "id": "ULID",
  "job_description_id": "ULID",
  "revision_number": 1,
  "raw_text": "preserved source text",
  "company": "string or null",
  "role": "string or null",
  "created_at": "UTC ISO-8601"
}
```

- `revision_number` increases monotonically within one logical Job Description.
- A revision is never updated or deleted while it supports historical
  reproducibility.
- Create/update requests use an approved deduplication and stale-write
  precondition so ambiguous retries cannot create accidental revisions.

## E2-CONTRACT-ANALYSIS-001 — Deterministic Analysis

```json
{
  "id": "ULID",
  "job_description_revision_id": "ULID",
  "analysis_schema_version": "string",
  "analysis_rule_version": "string",
  "signals": {
    "role": null,
    "required_skills": [],
    "nice_to_have_skills": [],
    "responsibilities": [],
    "keywords": [],
    "seniority": null,
    "soft_skills": [],
    "domain_context": []
  },
  "created_at": "UTC ISO-8601"
}
```

- Signal values carry only source-derived normalized content. The approved
  schema distinguishes absent, unknown, and empty where that affects behavior.
- One deterministic key identifies revision plus analysis-rule version.
- Failures use operation/error state and are never serialized as a successful
  partial Analysis.

## E2-CONTRACT-MATCH-001 — Immutable Match Report

```json
{
  "id": "ULID",
  "cv_version_id": "ULID",
  "job_description_id": "ULID",
  "job_description_revision_id": "ULID",
  "analysis_id": "ULID",
  "analysis_rule_version": "string",
  "matching_rule_version": "string",
  "report_schema_version": "string",
  "overall_score": "approved bounded numeric representation",
  "matched_skills": [],
  "missing_skills": [],
  "weak_evidence": [],
  "recommendations": [],
  "created_at": "UTC ISO-8601"
}
```

- Each classification carries normalized signal identity and source references
  sufficient to explain the outcome.
- Each available recommendation carries a related CV section identifier; it is
  advisory and never mutates a Profile or Version.
- Ordering, rounding, weights, aliases, evidence thresholds, and quality gates
  are frozen by versioned fixtures before implementation.

## E2-CONTRACT-ERROR-001 — Epic-specific failures

| Condition | Proposed status/code | Consumer action |
| --- | --- | --- |
| Hidden/absent Job Description, revision, CV Version, Analysis, or Match Report | `404 RESOURCE_NOT_FOUND` | Show non-disclosing not-found state |
| Stale Job Description update | `409 JOB_DESCRIPTION_UPDATE_CONFLICT` | Preserve input and offer controlled reload/retry |
| Deleted Job Description used for new work | `409 JOB_DESCRIPTION_DELETED` | Remove create/analyze actions and retain historical route where allowed |
| Current revision lacks successful Analysis | `409 JOB_DESCRIPTION_ANALYSIS_REQUIRED` | Offer Analyze action for current revision |
| Source/rule precondition changes during Match creation | `409 MATCH_SOURCE_CONFLICT` | Refresh selected sources and require explicit retry |

Validation, authentication, session expiry, rate limiting, malformed transport,
missing routes, and unexpected failures reuse Global codes. Exact route and
status/error matrices remain open under `E2-DEC-001` and `E2-DEC-007`.
An async-only `ANALYSIS_IN_PROGRESS` state is intentionally excluded from this
MVP contract and requires separately approved future scope.

## Proposed operation topology

| Operation | Proposed boundary | Success responsibility |
| --- | --- | --- |
| Create/list Job Descriptions | `POST/GET /api/v1/job-descriptions` | Create first revision / list active owned resources |
| Read/update/delete Job Description | `GET/PATCH/DELETE /api/v1/job-descriptions/{jobDescription}` | Read current, create revision, or logically delete |
| Analyze current revision | `POST /api/v1/job-descriptions/{jobDescription}/analyses` | Return or create successful deterministic Analysis |
| Read Analysis | Approved current/historical nested route | Return exact revision/rule result |
| Create/list Match Reports | `POST/GET /api/v1/match-reports` | Resolve exact sources atomically / list owned reports |
| Read Match Report | `GET /api/v1/match-reports/{matchReport}` | Return stored immutable output and source summary |

The topology is a proposal, not an approved endpoint contract. Stories may not
invent local variants while `E2-DEC-001` remains open.
