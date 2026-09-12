# Story 4.2: Answer Evidence questions — Contract Slice

Refines `E4-CONTRACT-EVIDENCE-001` and `E4-CONTRACT-ERROR-001`.

| Operation | Proposed request | Success | Failure |
| --- | --- | --- | --- |
| Submit Evidence | `POST /api/v1/evidence-interviews/{interview}/answers`; exact `question_id`, one answer mode, idempotency/precondition | `201` accepted answer with server timestamp/provenance and next/session state | Auth/not found, validation, stale/closed/out-of-order, duplicate/conflict |
| Read Interview progress | Owned Interview ID | Answers expose safe original User content, outcome, question/area, timestamp, provenance | Non-disclosing missing/foreign, unsupported schema |

API owns question currency, session transition, timestamp, provenance, and
validation. Frontend may keep an unsent draft but cannot create trusted Evidence
or infer supporting status. Freeze exact limits, answer/correction representation,
idempotency, progress, and error matrix through `E4-DEC-001`–`E4-DEC-003`.
