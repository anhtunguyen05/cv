# Epic 2 Security and Access

## E2-SEC-001 — Authentication and ownership

Every Epic 2 operation requires the approved Sanctum browser session. Laravel
policies authorize the logical Job Description, its revisions and Analyses,
the selected CV Version, and each Match Report before serialization or work.
Client-supplied owner IDs are ignored or rejected.

## E2-SEC-002 — Non-disclosure

Absent and foreign resource identifiers share `RESOURCE_NOT_FOUND` behavior.
Nested access must not reveal whether another User owns the parent, revision,
Analysis, CV Version, or report. List endpoints return only owned resources.

## E2-SEC-003 — Sensitive source content

Raw Job Description text and CV Version content are sensitive. They never enter
ordinary logs, analytics, exception payloads, cache keys, URLs, audit metadata,
or client telemetry. Only safe IDs, rule versions, outcome, duration, and a
correlation identifier may be logged.

## E2-SEC-004 — Safe rendering

Raw and derived text is rendered as text, never trusted HTML. The API validates
transport encoding and size before persistence/analysis; the web client does
not execute or interpolate source content into unsafe markup.

## E2-SEC-005 — Abuse and workload controls

Create/update/delete, analysis, and Match Report generation use separately
approved limits based on authenticated User plus trusted network context where
appropriate. A rate-limited or rejected request creates no revision, Analysis,
or Match Report. Exact keys/windows/cost budgets are owned by `E2-DEC-007`.

## E2-SEC-006 — No provider boundary

MVP Analysis and matching are local deterministic application services. No raw
CV or Job Description may be sent to an LLM/provider under an Epic 2 task. A
future provider integration requires separate approved scope and the Global
provider isolation contract.

## Access matrix

| Action | Owner + valid state | Non-owner/absent | Deleted logical JD |
| --- | --- | --- | --- |
| Create/list JD | Allowed / owned results only | N/A | Excluded from active list |
| Read/update JD | Allowed / current rules | `404` non-disclosing | New edit denied; historical route only where pinned |
| Analyze JD | Current active revision only | `404` non-disclosing | Denied |
| Create Match Report | Own CV Version + current analyzed active JD | `404` non-disclosing | Denied |
| Read existing report | Owner only | `404` non-disclosing | Allowed with deleted-parent context |

Security acceptance requires policy tests for direct and nested identifiers,
mixed-owner source pairs, deleted state, logs, and safe rendering.
