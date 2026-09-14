# Epic 4 Shared Contracts

These are proposed planning contracts. Exact fields, values, endpoints, provider
choices, retention, and launch controls require decisions and promotion to
stable documentation before implementation.

## E4-CONTRACT-INTERVIEW-001

An Interview contains stable ULID, server-derived owner, `match_report_id`,
`cv_version_id`, `job_description_revision_id`, `analysis_id`, immutable
improvement-area descriptors, `question_set_version`, status, and timestamps.
Start accepts only an owned `match_report_id` plus approved dedupe/precondition;
the server resolves every nested source.

## E4-CONTRACT-EVIDENCE-001

An answer request identifies the active `interview_id`, exact `question_id` and
question version, one of `answer` or `cannot_provide`, and an approved
idempotency/precondition value. Stored output preserves:

- original decoded User text separately from normalized validation/search view;
- `provenance = user`, timestamp, question/area/session/source identities;
- non-supporting outcome when Evidence is unavailable;
- append-only revision/audit behavior defined by `E4-DEC-003`.

## E4-CONTRACT-PROVIDER-001

The orchestrator constructs a minimum-data request containing approved source
fragments, Evidence references, Patch target allowlist, locale, and exact
`prompt_version`/`tool_schema_version`. The provider returns a DTO only. It gets
no database credentials, persistence tool, session secret, arbitrary URL fetch,
or authority to approve/apply.

## E4-CONTRACT-PATCH-001

```json
{
  "id": "ULID",
  "source_cv_version_id": "ULID",
  "match_report_id": "ULID",
  "interview_id": "ULID",
  "predecessor_patch_id": "ULID or null",
  "status": "pending_validation|pending|rejected|invalid|applied",
  "patch_schema_version": "string",
  "prompt_version": "string",
  "provider_model_version": "string",
  "target": {"section": "allowlisted", "field": "allowlisted", "item_id": "ULID or null"},
  "old_value": "typed exact source value",
  "new_value": "typed proposed value",
  "reason": "safe explanation",
  "evidence_source_ids": [],
  "provenance": {},
  "created_at": "UTC ISO-8601"
}
```

Provider output is validated before persistence as `pending`. Invalid output is
recorded only through the approved sanitized attempt/audit boundary, not as a
misleading pending Patch.

`pending_validation`, `pending`, `rejected`, and `applied` Patch records contain
the complete safely validated proposal fields. An `invalid` Patch is a formerly
safe application-owned Patch invalidated by a later edit or source/lifecycle
revalidation; malformed raw provider output never becomes a Patch aggregate.

## E4-CONTRACT-DECISION-001

- Read returns proposal/source diff, reason, Evidence references/provenance, and
  allowed actions for the exact current status.
- Edit accepts only allowed proposed values plus stale/idempotency precondition;
  it creates an attributable pending-validation revision or approved equivalent.
- Reject accepts explicit confirmation and precondition; repeated same decision
  is idempotent, conflicting decisions return a stable conflict.

## E4-CONTRACT-APPLY-001

Approval accepts Patch identity and stale/idempotency precondition, never a
client-composed CV snapshot. Laravel atomically revalidates and writes one new
immutable CV Version plus applied Patch/provenance. The result returns source
and new Version IDs and unchanged source identity.

## E4-CONTRACT-ERROR-001

| Condition | Proposed status/code | Required behavior |
| --- | --- | --- |
| Missing/foreign nested source | `404 ..._NOT_FOUND` | Non-disclosing; no provider/state change |
| No unresolved area | `409 INTERVIEW_NOT_NEEDED` | Explain and create no session |
| Stale/closed question/session | `409 EVIDENCE_SESSION_CONFLICT` | Preserve input; refresh exact state |
| Invalid Evidence/Patch edit | `422 VALIDATION_FAILED` | Stable field paths; no trusted transition |
| Provider timeout/unavailable | `503 PATCH_PROVIDER_UNAVAILABLE` | Safe retry classification; no pending Patch |
| Provider malformed/unsupported/ungrounded | `422 PATCH_PROPOSAL_INVALID` or approved terminal class | Sanitized generation-attempt failure; no Patch aggregate |
| Concurrent/stale Patch decision | `409 PATCH_STATE_CONFLICT` | Return current safe state/action |
| Old value/source mismatch | `409 PATCH_SOURCE_STALE` | No Version and no applied status |
| Apply transaction failure | stable retryable server error | Roll back Version/Patch/provenance together |

Exact operation topology and matrices remain open under `E4-DEC-001`,
`E4-DEC-004`, `E4-DEC-006`, and `E4-DEC-007`.
