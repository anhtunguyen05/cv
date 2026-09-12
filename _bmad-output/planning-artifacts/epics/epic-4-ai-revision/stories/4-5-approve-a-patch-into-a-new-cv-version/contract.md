# Story 4.5: Approve a Patch into a new CV Version — Contract Slice

Refines `E4-CONTRACT-APPLY-001` and `E4-CONTRACT-ERROR-001`.

| Operation | Proposed request | Success | Failure |
| --- | --- | --- | --- |
| Approve Patch | Explicit decision endpoint for owned Patch; confirmation intent plus stale/idempotency precondition, optional approved Version name | `201` or approved idempotent response with `source_cv_version_id`, `result_cv_version_id`, `patch_id`, applied status/provenance | Non-disclosing auth/source, invalid status/schema/Evidence, stale old value, concurrent conflict, validation, retryable transaction failure |
| Read result after lost response | Patch/read or idempotency reconciliation boundary | Same applied Patch and result Version | Current safe status; never create another Version blindly |

The API ignores client result content and recomputes the new snapshot by applying
the allowlisted Patch to its pinned source inside the transaction. Frontend
cannot set applied status and must distinguish stale conflict from retryable
failure. Freeze naming, preconditions, idempotency, status/error and provenance
through `E4-DEC-001`, `E4-DEC-004`, `E4-DEC-007`.
