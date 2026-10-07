# Epic 2 Shared Contracts

These are the **approved implementation contracts** for Epic 2. Global
envelopes, identifiers, timestamps, and pagination remain governed by
`docs/contracts/common/http.md`.

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

- A Job Description is an owner-only logical resource. `company` and `role`
  are read projections of `current_revision`, never independently mutable root
  fields.
- The active list has `page=1`, `per_page=20` by default, accepts at most 100,
  and orders by `updated_at DESC, id DESC`.
- `GET /job-descriptions/{id}` returns an active resource only. A deleted
  resource is non-disclosing `404`; deleted-source context appears only on an
  owner-readable historical Match Report.

## E2-CONTRACT-JD-REVISION-001 — Immutable revision and writes

```json
{
  "id": "ULID",
  "job_description_id": "ULID",
  "revision_number": 1,
  "raw_text": "transport-decoded source text",
  "company": "string or null",
  "role": "string or null",
  "created_at": "UTC ISO-8601"
}
```

- `raw_text` is stored exactly after JSON transport decoding. A derived
  validation/analysis view uses NFC plus CRLF-to-LF; it never replaces source.
- Source text must be non-empty after Unicode-aware trimming, contain no more
  than 50,000 code points or 200 KiB UTF-8. `company` and `role` are nullable;
  a supplied non-empty value is outer-trimmed and limited to 160 code points.
- `POST`, `PATCH`, and analysis/report creation require an `Idempotency-Key`
  UUID v4. The same key plus request fingerprint replays the original result;
  a changed fingerprint returns `409 IDEMPOTENCY_KEY_REUSED`.
- The server stores a keyed fingerprint and the original status/body for 24
  hours; it never logs the key, raw source, or request body. `POST` requests
  return `201` on first success and the saved `201` response on replay.
- `PATCH` and `DELETE` require `If-Match: "<current_revision_id>"`. A stale
  value returns `409 JOB_DESCRIPTION_UPDATE_CONFLICT` and writes nothing.
  A patch with no semantic source/metadata change returns
  `422 JOB_DESCRIPTION_NO_CHANGES`.
- A patch may contain only `raw_text`, `company`, and `role`. An omitted field
  preserves its current value; `null` clears `company` or `role`; `raw_text`
  cannot be null. Any accepted semantic change creates exactly one revision.

## E2-CONTRACT-ANALYSIS-001 — Deterministic Analysis

```json
{
  "id": "ULID",
  "job_description_revision_id": "ULID",
  "analysis_schema_version": "1.0.0",
  "analysis_rule_version": "1.0.0",
  "signals": {
    "role": { "state": "detected|absent|unknown", "value": null },
    "required_skills": { "state": "detected|absent|unknown", "items": [] },
    "nice_to_have_skills": { "state": "detected|absent|unknown", "items": [] },
    "responsibilities": { "state": "detected|absent|unknown", "items": [] },
    "keywords": { "state": "detected|absent|unknown", "items": [] },
    "seniority": { "state": "detected|absent|unknown", "value": null },
    "soft_skills": { "state": "detected|absent|unknown", "items": [] },
    "domain_context": { "state": "detected|absent|unknown", "items": [] }
  },
  "created_at": "UTC ISO-8601"
}
```

- `detected` requires source-supported normalized items; `absent` means the
  evaluated source contains no qualifying signal; `unknown` means conflicting
  or unsupported wording prevents a conclusion. Arrays use `items: []` when
  not detected and scalars use `value: null`.
- Rules use only a versioned closed vocabulary and phrase patterns. They
  normalize, deduplicate, and order items by canonical signal ID. They never
  infer a claim from an unrecognized term.
- One successful result exists per `(revision_id, analysis_rule_version)`.
  Analysis is synchronous, has a five-second deadline, and never exposes a
  partial result as successful.

## E2-CONTRACT-MATCH-001 — Immutable Match Report

```json
{
  "id": "ULID",
  "cv_version_id": "ULID",
  "job_description_id": "ULID",
  "job_description_revision_id": "ULID",
  "analysis_id": "ULID",
  "analysis_rule_version": "1.0.0",
  "matching_rule_version": "1.0.0",
  "report_schema_version": "1.0.0",
  "overall_score": 0.0,
  "matched_skills": [],
  "missing_skills": [],
  "weak_evidence": [],
  "recommendations": [],
  "created_at": "UTC ISO-8601"
}
```

- Score is bounded `0.00`–`100.00`, persisted at two decimal places and
  displayed at one. Weights are required skills 50%, preferred skills 10%,
  project/experience evidence 25%, seniority 10%, and role/domain 5%.
- Within a category, normalized signals share that category's weight equally;
  Strong, Weak, and Missing contribute `1`, `0.5`, and `0`. A category whose
  JD signal state is `absent` or `unknown` is excluded and remaining applicable
  category weights are renormalized to 100. Round only the final score.
- Strong evidence is a normalized signal in an immutable CV Version's project
  or experience content. A signal found only in a skills list or summary is
  Weak Evidence; absent signals are Missing.
- Every classification contains a canonical signal ID and source references.
  Recommendations contain `priority`, `target_cv_section`,
  `related_signal_ids`, `rationale`, and `action`; they are advisory only.
  The report never claims an ATS pass or predicts a score increase.
- Ordering is missing required, weak required, missing preferred, then matched;
  ties use canonical signal ID. Historic reads return stored output only.

## E2-CONTRACT-ERROR-001 — Epic-specific failures

| Condition | Status/code | Consumer action |
| --- | --- | --- |
| Hidden/absent owned resource | `404 RESOURCE_NOT_FOUND` | Non-disclosing not-found state |
| Stale Job Description write | `409 JOB_DESCRIPTION_UPDATE_CONFLICT` | Preserve input; reload explicitly |
| Reused idempotency key with another request | `409 IDEMPOTENCY_KEY_REUSED` | Stop retry; use a new key |
| Deleted JD used for new work | `409 JOB_DESCRIPTION_DELETED` | Remove action; retain existing report access |
| No successful current-revision Analysis | `409 JOB_DESCRIPTION_ANALYSIS_REQUIRED` | Offer explicit Analyze action |
| Pinned source changes during report creation | `409 MATCH_SOURCE_CONFLICT` | Refresh sources; require explicit retry |
| Synchronous derived operation unavailable by deadline | `503 DERIVATION_TEMPORARILY_UNAVAILABLE` | Offer user-controlled retry |

All `/api/v1` errors retain the global JSON envelope. Analysis and Match Report
creation are capped at 10 requests/minute per authenticated User plus trusted
network context; JD mutations are capped at 30. Throttling writes no state.

## Operation topology

| Operation | Route | Success |
| --- | --- | --- |
| Create/list JD | `POST/GET /api/v1/job-descriptions` | `201` create; paginated active list |
| Read/update/delete JD | `GET/PATCH/DELETE /api/v1/job-descriptions/{jobDescription}` | active detail; new revision; `204` delete |
| Analyze current revision | `POST /api/v1/job-descriptions/{jobDescription}/analyses` | `200` stored or replayed Analysis |
| Read Analysis | `GET /api/v1/job-descriptions/{jobDescription}/analyses/{analysis}` | exact owner-readable Analysis |
| Create/list reports | `POST/GET /api/v1/match-reports` | `201` create; paginated owner list |
| Read report | `GET /api/v1/match-reports/{matchReport}` | immutable output plus source summary |

No generic revision-history route is part of MVP. The exact source revision is
available through the owner-readable Match Report that pins it.
