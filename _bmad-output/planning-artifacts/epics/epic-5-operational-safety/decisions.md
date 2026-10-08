# Epic 5 Decisions and Coordination

The entries below are the self-directed disposable-MVP decisions requested by
the user. Production rollout still requires an independent owner, approver,
and dated evidence for each applicable decision.

## Decision register

| ID | Status | Owner | Decision required | Recommended starting point | Resolution | Evidence | Blocks |
| --- | --- | --- | --- | --- | --- | --- | --- |
| E5-DEC-001 | `approved` | `user-directed Codex implementation` | Operator identity, RBAC, access and evidence export | Freeze roles/auth strength/environment scope/separation/audit/query/export/revocation; do not assume an admin surface | Local MVP has no public operator endpoint or export. Any future operational read route must require a dedicated `operator` capability and remain read-only; normal User sessions never gain operator actions. | User instruction, 2026-10-08 | 5.1, 5.3, 5.4, 5.7 |
| E5-DEC-002 | `approved` | `user-directed Codex implementation` | Audit event, redaction, storage and retention | Freeze schema/taxonomy/references/redaction version, append-only store, fail-open/closed, query, retention, access and canary gate | Use versioned sanitized events in a dedicated append-only store; redaction failure is fail-closed; no raw request/response/content/secret fallback; operational records retain 30 days in disposable MVP evidence. | User instruction, 2026-10-08 | 5.1–5.4, 5.7 |
| E5-DEC-003 | `approved` | `user-directed Codex implementation` | Provider failure and optional async job lifecycle | Freeze category/status, timeout/retry/backoff/cancel/lease/late/lost/dedupe/result, User/operator state; instantiate only for named async work | The named operation is synchronous `patch-proposal`: at most two retry candidates within a one-minute window, no retry for malformed/validation output, and no trusted result without server validation. No generic queue or async worker is introduced; async remains explicitly deferred. | User instruction, 2026-10-08 | 5.2, 5.4, 5.7 |
| E5-DEC-004 | `approved` | `user-directed Codex implementation` | Data inventory, retention, deletion and legal policy | Freeze classes/stores/processors, authority, clocks/periods, action/order, holds/backups/external propagation, consent/notice, dry-run/approval/recovery/audit | Trusted product data is retained until an explicit scoped deletion request; operational audit/provider-attempt data is 30 days; idempotency data is 24 hours; holds prevent deletion; only dry-run/disposable execution is enabled, with no external/backup mutation. | User instruction, 2026-10-08 | 5.3, 5.7 |
| E5-DEC-005 | `approved` | `user-directed Codex implementation` | Telemetry, SLO, alert and runbook contract | Freeze metric taxonomy/units/labels/cardinality/sampling/aggregation/SLOs/alerts/no-data/owner/runbook/vendor/retention/privacy | Use a local versioned metric event boundary with bounded labels and explicit `no_data`; no vendor credentials or production dashboard are configured. A future sink must preserve the same schema and canary rejection. | User instruction, 2026-10-08 | 5.4, 5.7 |
| E5-DEC-006 | `approved` | `Pc` | Deterministic matching evaluation | Freeze corpus/expected outputs, metrics/counter-metrics/thresholds, tool/fixture/rule versions, diagnostics, CI cadence, approval/change policy | Use `match-report-v1.json` (40 reviewed + 12 held-out cases) as the synthetic corpus. Every case must match expected classifications/order and score within 0.01 across two independent runs; any unsupported-claim counterexample, incompatible version, non-repeatable result, product write, or clean CI run exceeding 60 seconds fails. Baseline changes require a separately versioned corpus, non-author approval, evidence, rollback path, and a waiver expiring within 7 days. | Pc, 2026-10-08 | 5.5, 5.7 |
| E5-DEC-007 | `approved` | `user-directed Codex implementation` | Single-orchestrator measurement and ADR | Freeze workloads, quality/latency/cost/reliability/operability metrics, baseline versions, limitation threshold, alternatives, safety review, decision/trigger/owner | Defer multi-agent adoption: no reproducible material limitation has been demonstrated; the existing deterministic/single-orchestrator path remains the approved baseline and any future adoption requires a new measurement/ADR. | User instruction, 2026-10-08 | 5.6, 5.7 |
| E5-DEC-008 | `approved` | `user-directed Codex implementation` | Operational environments and test safety | Freeze local/CI/staging/prod-like scopes, disposable data, external fakes/sandboxes, secrets, cleanup, fault injection, artifact access/retention | Verification is limited to local/CI disposable fixtures, fake providers, test database transactions, no credentials, and seven-day JSON evidence retention. Production-like traffic, external storage, and destructive runs are prohibited. | User instruction, 2026-10-08 | all verification tasks |
| E5-DEC-009 | `approved` | `user-directed Codex implementation` | Baseline verdict, freshness and waiver policy | Freeze required artifacts, evidence age, pass/pass-with-gaps/fail, approvers, waivers/expiry, unresolved-decision handling, release/kill criteria | Baseline verdict is `pass_with_gaps` for the disposable MVP until real provider/async/vendor/operator approvals exist; every gap remains listed with owner, risk, remediation and expiry. | User instruction, 2026-10-08 | 5.7 |

## Decision-gate refinement plan

### Approved safe baseline for E5-DEC-008

The current implementation uses only disposable local/CI execution: synthetic
fixtures checked into the repository, Laravel's testing environment, no
provider credentials, no production database/storage, and no operator export.
The quality artifact is JSON-only, content-free, retained for seven days, and
fails closed when its command exits non-zero. This is the approved implementation
boundary for the self-directed MVP; production-like verification remains out
of scope.

The table below is the decision sequence for full-Epic delivery. Each decision
is recorded as the implementation decision for this self-directed MVP; a
future production rollout still needs independent owner evidence.

| Gate | Decisions | Required accountable role | Minimum decision output | Enables |
| --- | --- | --- | --- | --- |
| G1 — safe planning environment | `E5-DEC-008` | user-directed Codex implementation | disposable local/CI matrix, fake dependency rule, secret and artifact handling, cleanup and fault-injection boundary | all implementation verification planning |
| G2 — deterministic MVP quality | `E5-DEC-006` | user-directed Codex implementation | pinned corpus, thresholds, change/waiver policy, runner cadence and artifact destination | Story 5.5 candidate for commitment |
| G3 — operator and data policy | `E5-DEC-001`, `E5-DEC-004` | user-directed Codex implementation | no public operator surface; fixed data classes/periods/holds and dry-run-only deletion | Stories 5.1 and 5.3; operator portions of 5.4 |
| G4 — provider control contracts | `E5-DEC-002`, `E5-DEC-003` | user-directed Codex implementation | sanitized audit boundary, classifier, bounded synchronous retry, explicit no-async decision | Stories 5.1 and provider portion of 5.2 |
| G5 — observability operation | `E5-DEC-005` | user-directed Codex implementation | local metric boundary, bounded labels, no-data semantics, no vendor integration | Story 5.4 and signal integration in 5.2 |
| G6 — orchestration evidence | `E5-DEC-007` | user-directed Codex implementation | explicit defer ADR and no runtime/config component | Story 5.6; no multi-agent component is authorized by this gate |
| G7 — release verdict | `E5-DEC-009` | user-directed Codex implementation | `pass_with_gaps`, seven-day evidence, explicit gap/owner/expiry policy | Story 5.7 after committed controls provide evidence |

### Selection rule

- Story 5.5 is approved for implementation because G1, G2, and
  `E5-PREREQ-MATCH-001` are satisfied.
- Stories 5.1–5.4 and 5.6 may implement only the bounded MVP surfaces described
  in the approved decisions; production provider traffic, operator APIs,
  destructive deletion, vendor monitoring, and generic queues remain disabled.
- Story 5.7 remains an integration/readiness gate. It cannot convert an
  uncommitted Story into a passed control or authorize destructive production
  work.

## Cross-Epic prerequisites

### E5-PREREQ-MATCH-001 — Deterministic Match contract

- Source: Epic 2 matching/report contracts and `DISCOVERY-E2-001`.
- Checkpoint: approved fixture corpus, engine/rule/schema versions, expected
  classifications/scores, and read-only validator boundary.
- Approved source baseline: `e27910f1e3f760ec87e6773c31310b955b195780`
  (`main`, 2026-10-08). Every Story 5.5 implementation branch must descend
  from this commit or record a reviewed equivalent source pin.
- Status: satisfied for planning and implementation; `TASK-5-5-01` verifies the
  pin before runner work begins.

### E5-PREREQ-AI-001 — Controlled provider/Patch boundary

- Source: Epic 4 provider/Patch decisions and coordination records.
- Checkpoint: minimum-data request, provider/model/prompt/tool versions, failure
  taxonomy, Patch validation, sanitized audit references, and no direct writes.
- Status: satisfied for the disposable deterministic fake provider only; real
  provider activation remains disabled.
- Blocks: production provider activation and related baseline evidence.

### E5-PREREQ-ASYNC-001 — Named asynchronous operation

- Source: separately approved analysis/Export/provider job decision.
- Checkpoint: named owner, source/result contract, business justification, queue/
  worker boundary, and lifecycle consumer. Without it no generic job code is added.
- Status: explicitly deferred by E5-DEC-003; no async implementation is
  authorized in this MVP.
- Blocks: generic queue/worker deployment only; synchronous patch recovery is
  covered by the approved classifier and retry policy.

## Coordination records

- **E5-COORD-AUDIT-001:** Story 5.1 owns audit/redaction schema/store/query and
  fixtures; 5.2–5.4 consume it without local event variants.
- **E5-COORD-JOB-001:** Story 5.2 owns provider failure and conditional job
  lifecycle; 5.4 consumes stable categories/metrics.
- **E5-COORD-RETENTION-001:** Story 5.3 owns inventory/policy/executor/audit/runbook;
  one owner coordinates destructive paths and external processor fakes.
- **E5-COORD-TELEMETRY-001:** Story 5.4 owns metric/label/SLO/alert/runbook schema;
  producers use shared instrumentation and no content labels.
- **E5-COORD-QUALITY-001:** Story 5.5 owns synthetic matching corpus/validator/
  metric result; Epic 2 matcher consumes the gate without rewriting fixtures.
- **E5-COORD-ADR-001:** Story 5.6 owns workload measurements and orchestration ADR;
  no implementation begins from an unapproved recommendation.
- **E5-COORD-BASELINE-001:** Story 5.7 owns the evidence manifest/verdict only;
  individual Stories own their test artifacts and remediation.
- **E5-COORD-TEST-001:** One integration owner coordinates canaries, PostgreSQL/
  external fakes, fault injection, alert drills, evidence storage, and CI config.

## Discovered work outside current Story scope

- **DISCOVERY-E5-001 — Operator access capability:** define product/operator
  identity and RBAC implementation if no approved platform surface exists.
- **DISCOVERY-E5-002 — Observability vendor/topology:** evaluate approved storage,
  region, retention, cost, access, export, deletion, and exit strategy.
- **DISCOVERY-E5-003 — Production deletion run authorization:** create separate
  change/approval procedure after policy, dry-run, backup/recovery, and staging proof.
- **DISCOVERY-E5-004 — Async infrastructure:** remains deferred until a named
  consumer is approved; the MVP intentionally ships no queue/worker runtime.
