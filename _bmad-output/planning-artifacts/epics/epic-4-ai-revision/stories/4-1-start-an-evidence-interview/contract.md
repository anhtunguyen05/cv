# Story 4.1: Start an Evidence interview — Contract Slice

Refines `E4-CONTRACT-INTERVIEW-001` and `E4-CONTRACT-ERROR-001`.

| Operation | Proposed request | Success | Failure |
| --- | --- | --- | --- |
| Start Interview | `POST /api/v1/match-reports/{matchReport}/evidence-interviews`; approved idempotency/precondition only | `201` new or approved reconciled response with exact source/areas/questions/status | Auth/not found, not needed, stale source, duplicate/conflict, safe server failure |
| Read Interview | `GET /api/v1/evidence-interviews/{interview}` | Owned session, exact immutable sources, areas/questions/current status | Non-disclosing missing/foreign, unsupported schema |

API resolves every source and eligibility rule. Frontend never submits areas,
owner, CV/JD/Analysis IDs, or status. Freeze exact endpoint, response, question
representation, allowed actions, idempotency, cache, and error matrix through
`E4-DEC-001`, `E4-DEC-002` before `ready-for-dev`.
