# Epic 2 Decisions and Coordination

Recommendations are planning proposals, not approved requirements. Before a
dependent Story becomes ready for development, every applicable decision needs
one owner, an approved resolution, and evidence in the form
`approver, YYYY-MM-DD`.

## Decision register

| ID | Status | Owner | Decision required | Recommended starting point | Resolution | Evidence | Blocks |
| --- | --- | --- | --- | --- | --- | --- | --- |
| E2-DEC-001 | `open` | `unassigned` | Job Description, Analysis, and Match Report HTTP topology | Use `/api/v1/job-descriptions` and `/api/v1/match-reports`; analyze the current revision through a nested action; freeze request/response/include/pagination/status/error/cache matrices | `pending` | `pending` | 2.1–2.5 |
| E2-DEC-002 | `open` | `unassigned` | Job Description input contract | Freeze raw-text character/byte/newline/Unicode limits, company/role limits, trim/clear/null rules, unsafe markup handling, and stable validation paths | `pending` | `pending` | 2.1–2.3 |
| E2-DEC-003 | `open` | `unassigned` | Revision and concurrency policy | Prefer explicit current revision ID or timestamp precondition; freeze revision numbering, competing writes, idempotency/lost-response reconciliation, delete races, and historical read representation | `pending` | `pending` | 2.1–2.4 |
| E2-DEC-004 | `open` | `unassigned` | Analysis schema and rule version | Freeze signal schema, absent versus unknown, normalization/alias vocabulary, ordering/deduplication, rule-version format, deterministic key, and repeat-analysis behavior | `pending` | `pending` | 2.3–2.5 |
| E2-DEC-005 | `open` | `unassigned` | Matching algorithm and quality gate | Freeze score range/precision, weights, aliases, required versus optional signals, evidence thresholds, fixture corpus, quality threshold, and counter-metric acceptance | `pending` | `pending` | 2.4, 2.5, Epic 5 quality Story |
| E2-DEC-006 | `open` | `unassigned` | Explainability and recommendation contract | Freeze source evidence references, matched/missing/Weak Evidence taxonomy, recommendation-to-section mapping, empty groups, disclaimer copy, and deterministic ordering | `pending` | `pending` | 2.4, 2.5 |
| E2-DEC-007 | `open` | `unassigned` | Execution, retry, and abuse policy | Keep MVP synchronous; define timeout, rate limits, request deduplication, retryable/terminal codes, ambiguous success reconciliation, and measured async trigger | `pending` | `pending` | 2.1–2.5 |
| E2-DEC-008 | `open` | `unassigned` | List/history and deleted-source UX | Freeze active list filters/order/pagination, revision visibility, report source summaries, deleted-parent banner/actions, and cache invalidation | `pending` | `pending` | 2.1, 2.2, 2.5 |
| E2-DEC-009 | `open` | `unassigned` | Stable fixture and verification ownership | Assign owners for contract promotion, analysis/matching corpora, disposable MySQL, Vitest/Playwright harness, CI commands, and quality regression approval | `pending` | `pending` | all verification tasks |

## Cross-Epic prerequisites

### E2-PREREQ-AUTH-001 — Authenticated ownership boundary

- Source: Epic 1 `E1-COORD-AUTH-001` and `E1-CONTRACT-AUTH-001`.
- Required checkpoint: approved protected request/session behavior, Public User
  identity, and non-disclosing ownership convention.
- Blocks: every Epic 2 implementation task that reads or writes User data.

### E2-PREREQ-VERSION-001 — Immutable CV Version consumer boundary

- Source: Epic 1 `E1-COORD-VERSION-001` and `E1-CONTRACT-VERSION-001`.
- Required checkpoint: approved immutable snapshot schema, identity, detail
  operation, and backward-compatible reader policy.
- Blocks: Story 2.4 Match engine/report creation and Story 2.5 source display.

## Cross-Story coordination records

### E2-COORD-JD-001 — Logical Job Description and revisions

- Stories: `2-1-save-a-job-description`, `2-2-manage-saved-job-descriptions`,
  and `2-3-analyze-a-job-description` as consumer.
- Decision owner: `unassigned`.
- Resolution: `pending E2-DEC-001, E2-DEC-002, E2-DEC-003, E2-DEC-008`.
- Reserved boundary: migrations/models, revision transaction, ownership policy,
  API resource/Form Requests/routes, web API schema, query/cache keys, and
  canonical Job Description fixtures.
- Sequence/merge rule: Story 2.1 freezes and lands the aggregate/revision
  checkpoint; Story 2.2 owns new revision and delete transitions; Story 2.3
  reads immutable revisions without redefining them.

### E2-COORD-ANALYSIS-001 — Deterministic Analysis

- Stories: `2-3-analyze-a-job-description`, with Stories 2.4 and 2.5 as
  read-only consumers.
- Decision owner: `unassigned`.
- Resolution: `pending E2-DEC-004, E2-DEC-007, E2-DEC-009`.
- Reserved boundary: signal vocabulary/schema, parser/normalizer, Analysis
  persistence/key, endpoints, adapters, fixtures, and retry state.
- Sequence/merge rule: freeze fixture/schema first; Story 2.3 owns Analysis
  generation and persistence; later Stories consume the exact stored result.

### E2-COORD-MATCH-001 — Matching and explainability

- Stories: `2-4-generate-a-match-report` and
  `2-5-review-an-explainable-match-report`.
- Decision owner: `unassigned`.
- Resolution: `pending E2-DEC-005, E2-DEC-006, E2-DEC-007, E2-DEC-009`.
- Reserved boundary: matcher, evidence resolver, scoring/versioning, Match
  Report persistence/resource, frontend schema/state, report UI, and fixtures.
- Sequence/merge rule: Story 2.4 owns deterministic output and persistence;
  Story 2.5 renders the stored contract and cannot recompute classifications.

### E2-COORD-TEST-001 — Epic 2 deterministic verification

- Stories: all Epic 2 Stories.
- Decision owner: `unassigned`.
- Resolution: `pending E2-DEC-009` and accepted `E1-COORD-TEST-001` harness.
- Reserved boundary: versioned JD/Analysis/Match fixtures, MySQL orchestration,
  cross-layer contract tests, Playwright data, CI commands, and quality gate.
- Sequence/merge rule: one test-integration owner lands shared corpora/harness
  changes; Story owners add bounded scenarios without competing configurations.

## Discovered work outside current Story scope

### DISCOVERY-E2-001 — Deterministic matching quality baseline

- Status: `unassigned`.
- Owner: `unassigned`.
- Scope: curate and approve a representative fixture corpus, baseline expected
  classifications/scores, minimum quality threshold, and regression ownership.
- Reason externalized: quality governance spans Epic 2 implementation and Epic
  5 operational validation; it is not a local UI or endpoint task.
- Blocks: final approval of `E2-DEC-005` and Match verification completion.

### DISCOVERY-E2-002 — Async analysis decision trigger

- Status: `unassigned`.
- Owner: `unassigned`.
- Scope: define latency/error measurements that would justify queue/worker
  analysis and the future async job/result contract.
- Reason externalized: MVP remains synchronous under `REL-STD-004`; no current
  requirement authorizes worker or queue implementation.
