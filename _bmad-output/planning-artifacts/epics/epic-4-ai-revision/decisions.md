# Epic 4 Decisions and Coordination

The initial register below records the pre-baseline state. The approved
implementation baseline follows it and supersedes every `open`/`pending` entry
for planning and fake-provider implementation. Production provider activation
remains a separate Epic 5 release decision.

## Decision register

| ID | Status | Owner | Decision required | Recommended starting point | Resolution | Evidence | Blocks |
| --- | --- | --- | --- | --- | --- | --- | --- |
| E4-DEC-001 | `open` | `unassigned` | Interview/Patch API topology | Freeze endpoints, payload/envelope, include, status/error, idempotency/precondition, cache, and allowed-action matrices | `pending` | `pending` | 4.1–4.6 |
| E4-DEC-002 | `open` | `unassigned` | Interview eligibility and question contract | Freeze unresolved-area rules, question source/generation, ordering/version, session status/expiry/completion, restart, and no-interview-needed behavior | `pending` | `pending` | 4.1–4.3, 4.6 |
| E4-DEC-003 | `open` | `unassigned` | Evidence answer/provenance contract | Freeze raw/normalized limits, cannot-provide, edit/correction, timestamp, stale/out-of-order, dedupe, lost response, and valid-context rules | `pending` | `pending` | 4.2–4.6 |
| E4-DEC-004 | `open` | `unassigned` | Patch schema, allowlist, and lifecycle | Prefer one bounded target operation per Patch; freeze types/limits, old-value equality, citations, statuses, provider-invalid attempt versus later-invalidated Patch, proposal revision, lineage, and allowed transitions | `pending` | `pending` | 4.3–4.6 |
| E4-DEC-005 | `open` | `unassigned` | Provider/privacy/retention policy | Select provider/model/region; approve minimum fields, consent/legal basis, subprocessors, training/retention/deletion, raw payload policy, secrets, and audit metadata | `pending` | `pending` | provider tasks and production readiness |
| E4-DEC-006 | `open` | `unassigned` | Orchestrator execution/reliability | Freeze prompt/tool versions, token/cost/time/rate/concurrency limits, sync/async job contract, retry/cancel/dedupe, late result, fallback, and circuit behavior | `pending` | `pending` | 4.3, 4.6 |
| E4-DEC-007 | `open` | `unassigned` | Review/edit/reject/apply concurrency | Freeze allowed edit, confirmation, stale source/value, locks/preconditions, competing decisions, Version naming/provenance, lost success, and idempotent result | `pending` | `pending` | 4.4–4.6 |
| E4-DEC-008 | `open` | `unassigned` | AI quality and safety launch gate | Freeze groundedness/usefulness/factuality/refusal metrics, adversarial corpus, thresholds, human approver, rollout/kill switch, regression and model-change policy | `pending` | `pending` | 4.3–4.6 verification |
| E4-DEC-009 | `open` | `unassigned` | UX/accessibility and test ownership | Assign fixture, PostgreSQL, provider fake, accessibility, Playwright, CI, synthetic data, and evidence-retention owners | `pending` | `pending` | all verification tasks |

## Approved implementation baseline (2026-10-07)

Approved by `Pc`; evidence for each resolution is `Pc, 2026-10-07`.

| ID | Approved resolution | Effect |
| --- | --- | --- |
| E4-DEC-001 | Use `POST /match-reports/{matchReport}/evidence-interviews`, `GET /evidence-interviews/{interview}`, `POST /evidence-interviews/{interview}/answers`, `POST /evidence-interviews/{interview}/patches`, `GET/PATCH /patches/{patch}`, and explicit `reject`, `approve`, and `regenerate` actions. Every mutation requires `Idempotency-Key`; Patch mutations also require `If-Match` Patch revision. | Contract fixtures freeze exact envelopes and error matrices in the owning Stories. |
| E4-DEC-002 | A missing or Weak Evidence signal is an unresolved area. Server creates at most five deterministic template questions, one per area, under `question_set_version=1.0`. An active session reconciles for the exact report, expires after seven days, and completes once every question has an outcome. No area returns `409 INTERVIEW_NOT_NEEDED`; correction/restart creates a new Interview. | No model/provider is used to generate questions. |
| E4-DEC-003 | An answer is exactly `answer` or `cannot_provide`. Original submitted text is immutable; normalized text is derived only for validation/search. No answer-edit endpoint exists in MVP. | A closed or expired session is never reopened or rewritten. |
| E4-DEC-004 | One Patch changes exactly one bounded target: replace `summary`, replace one existing Experience/Project highlight, or append one highlight to an existing Experience/Project item. It carries exact old value when replacing, target item ULID when applicable, and cited supporting Evidence IDs. | Unsupported raw provider output is only a sanitized attempt; `invalid` is reserved for later revalidation of a persisted Patch. |
| E4-DEC-005 | Implement only a deterministic `PatchProposalProvider` fake. Do not select or call a production provider, send real User traffic, or persist raw prompts/outputs. | Provider/legal/retention/region decisions stay in `DISCOVERY-E4-001` and gate production activation only. |
| E4-DEC-006 | Generation is synchronous with a 15-second deadline, no worker/queue, and no automatic retry. Client-controlled replay is idempotent; timeout/unavailability creates no Patch. | Durable async work stays in `DISCOVERY-E4-003`. |
| E4-DEC-007 | Patch edits remain inside the same target and create a pending-validation revision. Reject and approve need explicit confirmation, `If-Match`, and idempotency. First committed terminal action wins; conflicts return `409 PATCH_STATE_CONFLICT`. | Apply locks and revalidates before atomically creating exactly one result Version. |
| E4-DEC-008 | Implementation acceptance uses synthetic adversarial cases, deterministic fake outputs, strict schema/citation validation, and zero accepted unsupported-claim cases. | Production thresholds, rollout approver, monitoring, and kill switch remain `DISCOVERY-E4-002` plus Epic 5. |
| E4-DEC-009 | `Pc` owns planning acceptance. Before a task is `doing`, one integration owner reserves each coordination boundary and task owners are named in `tasks.md`. Synthetic fixtures and the existing shared harness own fake-provider, PostgreSQL-race, accessibility, Playwright, and CI evidence. | This is an execution-start gate, not a planning blocker. |

## Cross-Epic prerequisites

### E4-PREREQ-VERSION-001 — Immutable CV Version and apply boundary

- Source: Epic 1 `E1-CONTRACT-VERSION-001`, `E1-COORD-VERSION-001`.
- Required checkpoint: complete immutable snapshot schema/validation, owned
  reader, atomic creation, naming, and provenance extension policy.
- Status: satisfied by Epic 1's completed `docs/contracts/cv/version-v1.md`;
  Epic 4 consumes it without changing historical Version semantics.
- Blocks: Interview source display, Patch old value, and approval.

### E4-PREREQ-MATCH-001 — Explainable Match Report source

- Source: Epic 2 `E2-CONTRACT-MATCH-001`, `E2-COORD-MATCH-001`.
- Required checkpoint: owned immutable report with exact source tuple,
  unresolved areas, classifications, recommendations, and Evidence references.
- Status: pending completion and acceptance of Epic 2 Story 2.5 review evidence.
- Blocks: Interview start and downstream generation.

### E4-PREREQ-OPS-001 — Provider operational safety

- Source: Epic 5 audit, provider/background failure, retention/deletion, and
  monitoring Stories.
- Required checkpoint: approved provider audit, privacy/retention, failure,
  cost/latency/quality monitoring, incident, and kill-switch controls.
- Blocks: production provider enablement, not planning-only decomposition.

## Cross-Story coordination records

### E4-COORD-INTERVIEW-001 — Interview and Evidence

- Stories: 4.1 owner; 4.2 owner of answer transition; 4.3/4.6 consumers.
- Decision owner: `unassigned`.
- Resolution: `pending E4-DEC-001 through E4-DEC-003`.
- Reserved boundary: migrations/models, eligibility/question service, Evidence
  write policy, APIs, web state, fixtures, and source/provenance DTOs.
- Sequence/merge rule: 4.1 lands session/question checkpoint; 4.2 lands answer
  transition; later Stories consume exact completed Evidence context.

### E4-COORD-PROVIDER-001 — Orchestrator and provider boundary

- Stories: 4.3 owner; 4.6 consumer.
- Decision owner: `unassigned`.
- Resolution: `pending E4-DEC-005, E4-DEC-006, E4-DEC-008`.
- Reserved boundary: provider interface/adapter, prompt/tool schema, minimum-data
  mapper, retry/cancel/dedupe, fake, telemetry, and adversarial fixtures.
- Sequence/merge rule: 4.3 lands one controlled contract; regeneration reuses it
  with predecessor/rejection context and cannot introduce another path.

### E4-COORD-PATCH-001 — Patch schema, lifecycle, and review

- Stories: 4.3 schema/generation owner; 4.4 decision owner; 4.5 apply consumer;
  4.6 lineage consumer.
- Decision owner: `unassigned`.
- Resolution: `pending E4-DEC-001, E4-DEC-004, E4-DEC-007`.
- Reserved boundary: Patch persistence/schema/validator/policies/resources,
  frontend adapter/diff/editor/state, transitions, and Patch fixtures.
- Sequence/merge rule: schema/validation before persistence; review transitions
  before apply/regenerate; no Story locally extends the allowlist.

### E4-COORD-APPLY-001 — Atomic Patch application

- Stories: 4.5, with Epic 1 Version boundary and 4.4 status provider.
- Decision owner: `unassigned`.
- Resolution: `pending E4-DEC-004, E4-DEC-007, E4-DEC-009`.
- Reserved boundary: Patch application service, snapshot transform/validation,
  locks/idempotency, Version provenance, migration constraints, and race tests.
- Sequence/merge rule: consume approved Version/Patch checkpoints; one owner
  integrates the transaction and result contract.

### E4-COORD-TEST-001 — Epic 4 safety and evaluation

- Stories: all Epic 4 Stories.
- Decision owner: `unassigned`.
- Resolution: `pending E4-DEC-008, E4-DEC-009` and accepted Epic 1/2 harnesses.
- Reserved boundary: synthetic source/Evidence/Patch corpus, provider fake,
  adversarial/quality evaluation, PostgreSQL races, Playwright, and CI evidence.

## Discovered work outside current Story scope

### DISCOVERY-E4-001 — Provider selection and production data approval

- Status: `unassigned`.
- Owner: `unassigned`.
- Scope: legal/security/product comparison of provider/model/region, data use,
  retention/deletion, subprocessors, cost/latency, contract, and exit strategy.
- Blocks: `E4-DEC-005` and any real provider traffic.

### DISCOVERY-E4-002 — Patch quality baseline and red-team corpus

- Status: `unassigned`.
- Owner: `unassigned`.
- Scope: human-reviewed groundedness/usefulness/factuality dataset, injection and
  unsupported-claim cases, launch thresholds, counter-metrics, and ownership.
- Blocks: `E4-DEC-008` and production acceptance.

### DISCOVERY-E4-003 — Async generation trigger

- Status: `unassigned`.
- Owner: `unassigned`.
- Scope: measure generation latency/failure/cost and define when a durable
  queue/worker job-result contract is justified.
- Reason externalized: execution mode is not assumed from provider use; any
  async infrastructure requires an approved decision and Epic 5 controls.
