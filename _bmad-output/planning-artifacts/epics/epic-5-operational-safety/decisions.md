# Epic 5 Decisions and Coordination

Recommendations are planning proposals. Before dependent work becomes ready,
each decision needs one owner, approved resolution, and `approver, YYYY-MM-DD` evidence.

## Decision register

| ID | Status | Owner | Decision required | Recommended starting point | Resolution | Evidence | Blocks |
| --- | --- | --- | --- | --- | --- | --- | --- |
| E5-DEC-001 | `open` | `unassigned` | Operator identity, RBAC, access and evidence export | Freeze roles/auth strength/environment scope/separation/audit/query/export/revocation; do not assume an admin surface | `pending` | `pending` | 5.1, 5.3, 5.4, 5.7 |
| E5-DEC-002 | `open` | `unassigned` | Audit event, redaction, storage and retention | Freeze schema/taxonomy/references/redaction version, append-only store, fail-open/closed, query, retention, access and canary gate | `pending` | `pending` | 5.1–5.4, 5.7 |
| E5-DEC-003 | `open` | `unassigned` | Provider failure and optional async job lifecycle | Freeze category/status, timeout/retry/backoff/cancel/lease/late/lost/dedupe/result, User/operator state; instantiate only for named async work | `pending` | `pending` | 5.2, 5.4, 5.7 |
| E5-DEC-004 | `open` | `unassigned` | Data inventory, retention, deletion and legal policy | Freeze classes/stores/processors, authority, clocks/periods, action/order, holds/backups/external propagation, consent/notice, dry-run/approval/recovery/audit | `pending` | `pending` | 5.3, 5.7 |
| E5-DEC-005 | `open` | `unassigned` | Telemetry, SLO, alert and runbook contract | Freeze metric taxonomy/units/labels/cardinality/sampling/aggregation/SLOs/alerts/no-data/owner/runbook/vendor/retention/privacy | `pending` | `pending` | 5.4, 5.7 |
| E5-DEC-006 | `open` | `unassigned` | Deterministic matching evaluation | Freeze corpus/expected outputs, metrics/counter-metrics/thresholds, tool/fixture/rule versions, diagnostics, CI cadence, approval/change policy | `pending` | `pending` | 5.5, 5.7 |
| E5-DEC-007 | `open` | `unassigned` | Single-orchestrator measurement and ADR | Freeze workloads, quality/latency/cost/reliability/operability metrics, baseline versions, limitation threshold, alternatives, safety review, decision/trigger/owner | `pending` | `pending` | 5.6, 5.7 |
| E5-DEC-008 | `open` | `unassigned` | Operational environments and test safety | Freeze local/CI/staging/prod-like scopes, disposable data, external fakes/sandboxes, secrets, cleanup, fault injection, artifact access/retention | `pending` | `pending` | all verification tasks |
| E5-DEC-009 | `open` | `unassigned` | Baseline verdict, freshness and waiver policy | Freeze required artifacts, evidence age, pass/pass-with-gaps/fail, approvers, waivers/expiry, unresolved-decision handling, release/kill criteria | `pending` | `pending` | 5.7 |

## Cross-Epic prerequisites

### E5-PREREQ-MATCH-001 — Deterministic Match contract

- Source: Epic 2 matching/report contracts and `DISCOVERY-E2-001`.
- Checkpoint: approved fixture corpus, engine/rule/schema versions, expected
  classifications/scores, and read-only validator boundary.
- Blocks: Story 5.5.

### E5-PREREQ-AI-001 — Controlled provider/Patch boundary

- Source: Epic 4 provider/Patch decisions and coordination records.
- Checkpoint: minimum-data request, provider/model/prompt/tool versions, failure
  taxonomy, Patch validation, sanitized audit references, and no direct writes.
- Blocks: Stories 5.1, provider part of 5.2, and related baseline evidence.

### E5-PREREQ-ASYNC-001 — Named asynchronous operation

- Source: separately approved analysis/Export/provider job decision.
- Checkpoint: named owner, source/result contract, business justification, queue/
  worker boundary, and lifecycle consumer. Without it no generic job code is added.
- Blocks: async implementation portion of Story 5.2 only.

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
- **E5-COORD-TEST-001:** One integration owner coordinates canaries, MySQL/
  external fakes, fault injection, alert drills, evidence storage, and CI config.

## Discovered work outside current Story scope

- **DISCOVERY-E5-001 — Operator access capability:** define product/operator
  identity and RBAC implementation if no approved platform surface exists.
- **DISCOVERY-E5-002 — Observability vendor/topology:** evaluate approved storage,
  region, retention, cost, access, export, deletion, and exit strategy.
- **DISCOVERY-E5-003 — Production deletion run authorization:** create separate
  change/approval procedure after policy, dry-run, backup/recovery, and staging proof.
- **DISCOVERY-E5-004 — Async infrastructure:** create only after
  `E5-PREREQ-ASYNC-001`; planning does not authorize queue/worker deployment.
