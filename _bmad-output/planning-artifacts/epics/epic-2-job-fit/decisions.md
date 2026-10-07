# Epic 2 Decisions and Coordination

The resolutions below are the approved implementation baseline for Epic 2.
They are recorded as accepted by Pc on 2026-10-07 and are reflected in the
implementation branch `feat/epic-2-job-fit`.

## Decision register

| ID | Status | Owner | Decision required | Recommended starting point | Resolution | Evidence | Blocks |
| --- | --- | --- | --- | --- | --- | --- | --- |
| E2-DEC-001 | `approved` | `Pc` | HTTP topology | Use the exact routes/statuses in `E2-CONTRACT-*`; no `include` parameter or generic revision route in MVP | `contracts.md` operation topology | Pc, 2026-10-07 | 2.1–2.5 |
| E2-DEC-002 | `approved` | `Pc` | JD input contract | Preserve transport-decoded raw text; derived NFC/CRLF validation; 50,000 code points/200 KiB; optional company/role max 160 | `E2-CONTRACT-JD-REVISION-001` | Pc, 2026-10-07 | 2.1–2.3 |
| E2-DEC-003 | `approved` | `Pc` | Revision/concurrency | `If-Match` current revision plus UUID-v4 idempotency key; no-op patch rejected; logical delete has the same stale guard | `E2-CONTRACT-JD-REVISION-001` | Pc, 2026-10-07 | 2.1–2.4 |
| E2-DEC-004 | `approved` | `Pc` | Analysis schema/rule | Schema/rule `1.0.0`; closed vocabulary/patterns; `detected/absent/unknown`; one result per revision/rule | `E2-CONTRACT-ANALYSIS-001` | Pc, 2026-10-07 | 2.3–2.5 |
| E2-DEC-005 | `approved` | `Pc` | Match algorithm/quality | 0–100 score; 50/10/25/10/5 weight split; Strong/Weak/Missing evidence; fixture quality gate below | `E2-CONTRACT-MATCH-001`, `E2-TEST-003` | Pc, 2026-10-07 | 2.4, 2.5, Epic 5 quality Story |
| E2-DEC-006 | `approved` | `Pc` | Explainability | Canonical signal/source references; advisory recommendations; deterministic group ordering; no ATS/pass/boost claims | `E2-CONTRACT-MATCH-001` | Pc, 2026-10-07 | 2.4, 2.5 |
| E2-DEC-007 | `approved` | `Pc` | Execution/retry/abuse | Synchronous five-second deadline; 10/min derived work, 30/min JD writes; user-controlled replay/retry only | `E2-CONTRACT-ERROR-001` | Pc, 2026-10-07 | 2.1–2.5 |
| E2-DEC-008 | `approved` | `Pc` | List/history/deleted UX | Active page-number list, default 20/max 100; deleted resources only appear through pinned reports | `E2-CONTRACT-JD-001` | Pc, 2026-10-07 | 2.1, 2.2, 2.5 |
| E2-DEC-009 | `approved` | `Pc` | Fixture/verification ownership | Pc approves contracts/corpus; Codex owns Epic 2 integration fixtures, harness, and cross-layer gate | `E2-COORD-TEST-001` | Pc, 2026-10-07 | all verification tasks |

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
- Decision owner: `Pc`; implementation integration owner: `Codex` on `feat/epic-2-job-fit`.
- Resolution: proposed by `E2-DEC-001`, `E2-DEC-002`, `E2-DEC-003`, and
  `E2-DEC-008`; Story 2.1 owns creation, Story 2.2 owns later revisions and
  logical deletion, and Story 2.3 reads revisions without redefining them.
- Reserved boundary: migrations/models, revision transaction, ownership policy,
  API resource/Form Requests/routes, web API schema, query/cache keys, and
  canonical Job Description fixtures.
- Sequence/merge rule: Story 2.1 freezes and lands the aggregate/revision
  checkpoint; Story 2.2 owns new revision and delete transitions; Story 2.3
  reads immutable revisions without redefining them.

### E2-COORD-ANALYSIS-001 — Deterministic Analysis

- Stories: `2-3-analyze-a-job-description`, with Stories 2.4 and 2.5 as
  read-only consumers.
- Decision owner: `Pc`; implementation integration owner: `Codex` on `feat/epic-2-job-fit`.
- Resolution: proposed by `E2-DEC-004`, `E2-DEC-007`, and `E2-DEC-009`.
- Reserved boundary: signal vocabulary/schema, parser/normalizer, Analysis
  persistence/key, endpoints, adapters, fixtures, and retry state.
- Sequence/merge rule: freeze fixture/schema first; Story 2.3 owns Analysis
  generation and persistence; later Stories consume the exact stored result.

### E2-COORD-MATCH-001 — Matching and explainability

- Stories: `2-4-generate-a-match-report` and
  `2-5-review-an-explainable-match-report`.
- Decision owner: `Pc`; implementation integration owner: `Codex` on `feat/epic-2-job-fit`.
- Resolution: proposed by `E2-DEC-005`, `E2-DEC-006`, `E2-DEC-007`, and
  `E2-DEC-009`.
- Reserved boundary: matcher, evidence resolver, scoring/versioning, Match
  Report persistence/resource, frontend schema/state, report UI, and fixtures.
- Sequence/merge rule: Story 2.4 owns deterministic output and persistence;
  Story 2.5 renders the stored contract and cannot recompute classifications.

### E2-COORD-TEST-001 — Epic 2 deterministic verification

- Stories: all Epic 2 Stories.
- Decision owner: `Pc`; implementation integration owner: `Codex` on `feat/epic-2-job-fit`.
- Resolution: proposed by `E2-DEC-009`; consume the accepted
  `E1-COORD-TEST-001` harness rather than introducing a competing one.
- Reserved boundary: versioned JD/Analysis/Match fixtures, PostgreSQL orchestration,
  cross-layer contract tests, Playwright data, CI commands, and quality gate.
- Sequence/merge rule: one test-integration owner lands shared corpora/harness
  changes; Story owners add bounded scenarios without competing configurations.

### E2-COORD-JD-DELETE-001 — Early logical-deletion checkpoint

- Producer: `TASK-2-2-02`; consumers: `TASK-2-5-02` and `TASK-2-5-03`.
- Decision owner: `Pc`; implementation integration owner: `Codex` on `feat/epic-2-job-fit`.
- Resolution: the producer checkpoint is satisfied when the approved fixtures,
  PostgreSQL feature evidence, and API contract prove active-list exclusion,
  new-work denial, and owner-only pinned-report source resolution. It does not
  wait for Story 2.2's report-history UI journey.
- Sequence/merge rule: Story 2.5 may consume this checkpoint after
  `TASK-2-2-02` is `done`. Story 2.2's `TASK-2-2-03` remains the consumer of
  Story 2.5's finished report-view contract. This removes the former cycle.

## Discovered work outside current Story scope

### DISCOVERY-E2-001 — Deterministic matching quality baseline

- Status: `approved` (2026-10-07).
- Owner: `Pc` for approval; integration owner assigned before implementation.
- Scope: curate and approve a representative fixture corpus, baseline expected
  classifications/scores, minimum quality threshold, and regression ownership.
- Reason externalized: quality governance spans Epic 2 implementation and Epic
  5 operational validation; it is not a local UI or endpoint task.
- Resolution: `docs/contracts/jd/fixtures/match-report-v1.json` contains 40
  reviewed and 12 held-out cases. SQLite and disposable PostgreSQL runners
  execute every case twice, assert classifications, score, source pins,
  recommendation shape, and canonical replay. The baseline is accepted for
  Epic 2 and retained as Epic 5 regression input.
- Blocks: none for Epic 2 implementation.

### DISCOVERY-E2-002 — Async analysis decision trigger

- Status: `proposed`.
- Owner: `Pc` for approval; integration owner assigned before implementation.
- Scope: define latency/error measurements that would justify queue/worker
  analysis and the future async job/result contract.
- Reason externalized: MVP remains synchronous under `REL-STD-004`; no current
  requirement authorizes worker or queue implementation.
