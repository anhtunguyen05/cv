# Story 5.1 — Tasks

## Tasks & Acceptance

**Execution:**

- [ ] TASK-5-1-01: Freeze audit, redaction, and query fixtures
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `none`
  - Covers: `AC-5-1-audit-ai-tool-calls-safely-01`, `AC-5-1-audit-ai-tool-calls-safely-02`
  - Scope: 01. Freeze audit, redaction, operator-query, and canary fixtures: event fields/outcomes/failures/canaries/query/RBAC/retention/export matrices
  - Coordination: `E5-COORD-AUDIT-001`, `E5-COORD-TEST-001`
  - Blocked by: `E5-PREREQ-AI-001`; `E5-DEC-001`; `E5-DEC-002`; `E5-DEC-004`; `E5-DEC-008`; `DISCOVERY-E5-001`
  - Outcome: 01. Freeze audit, redaction, operator-query, and canary fixtures: One approved safe audit contract and forbidden-content corpus.
  - Acceptance: 01. Freeze audit, redaction, operator-query, and canary fixtures: every field has source/classification/cardinality/redaction/retention rule.
  - Verification: 01. Freeze audit, redaction, operator-query, and canary fixtures: schema/threat/privacy/operator review evidence.

- [ ] TASK-5-1-02: Deliver privacy-safe audit storage and query
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-5-1-01`
  - Covers: `AC-5-1-audit-ai-tool-calls-safely-01`, `AC-5-1-audit-ai-tool-calls-safely-02`
  - Scope: 01. Implement versioned audit event builder and redactor: allowlist mapper, outcome/category/version/correlation, nested redaction, reject/fallback behavior | 02. Implement append-only audit persistence and retention hooks: model/migration/store, uniqueness/indexes, fail policy, retention classification, no product mutation | 03. Integrate provider/tool outcome audit emission: success/failure/cancel/timeout correlation around approved orchestrator boundary | 04. Expose authorized read-only audit query API: operator policy, access audit, bounded filters/pagination, resource/controller/routes and safe export guard | 05. Verify redaction, isolation, append-only, and access controls: all sinks/exports, canaries, two Users/operators/roles/environments, failure/idempotency/retention
  - Coordination: `E5-COORD-AUDIT-001`, `E5-COORD-RETENTION-001`, `E5-COORD-TEST-001`; one audit integration owner coordinates event builder/redactor, append-only store, provider emission and query API, then the complete privacy/isolation evidence set
  - Blocked by: `E5-DEC-001`; `DISCOVERY-E5-001`; `E5-DEC-008`
  - Outcome: 01. Implement versioned audit event builder and redactor: Produce safe normalized events from provider outcomes. | 02. Implement append-only audit persistence and retention hooks: Store immutable operational metadata separately from trusted state. | 03. Integrate provider/tool outcome audit emission: Emit exactly one reconciled safe outcome per approved attempt. | 04. Expose authorized read-only audit query API: Serve non-authoritative audit metadata only to approved operators. | 05. Verify redaction, isolation, append-only, and access controls: Prove useful audit without sensitive leakage or trusted writes.
  - Acceptance: 01. Implement versioned audit event builder and redactor: all canaries and malformed/unbounded input fail closed from sinks. | 02. Implement append-only audit persistence and retention hooks: updates/product foreign keys cannot turn an event into a write path. | 03. Integrate provider/tool outcome audit emission: no raw request/response/secret crosses the event boundary. | 04. Expose authorized read-only audit query API: access is audited; no raw search/export or product mutation endpoint exists. | 05. Verify redaction, isolation, append-only, and access controls: forbidden-canary count is zero and unauthorized access/mutation fails.
  - Verification: 01. Implement versioned audit event builder and redactor: unit/property/fuzz/canary tests. | 02. Implement append-only audit persistence and retention hooks: PostgreSQL constraint, append-only, outage, and retention-hook tests. | 03. Integrate provider/tool outcome audit emission: provider-fake integration and sink-canary scans. | 04. Expose authorized read-only audit query API: RBAC/Laravel API/query/export-guard tests. | 05. Verify redaction, isolation, append-only, and access controls: approved security/PostgreSQL/Laravel/Vitest commands and scan artifact.

- [ ] TASK-5-1-03: Deliver and verify operator audit journey
  - Status: `todo`
  - Owner: `unassigned`
  - Branch/worktree: `unassigned`
  - Depends on: `TASK-5-1-01`
  - Covers: `AC-5-1-audit-ai-tool-calls-safely-01`, `AC-5-1-audit-ai-tool-calls-safely-02`
  - Scope: 01. Build accessible read-only operator audit experience: schema adapter, filters/pagination, loading/empty/error/denied/detail, operational label, keyboard/readability | 02. Verify operator audit journey end to end: provider outcome-to-audit query/filter/denial/safe-detail/keyboard scenarios
  - Coordination: `E5-COORD-AUDIT-001`, `E5-COORD-TEST-001`
  - Blocked by: `E5-DEC-001`; `DISCOVERY-E5-001`; `E5-DEC-008`
  - Outcome: 01. Build accessible read-only operator audit experience: Let approved operators understand safe audit data without a write path. | 02. Verify operator audit journey end to end: Verify the complete safe read-only operational audit flow.
  - Acceptance: 01. Build accessible read-only operator audit experience: every fixture is accessible, clearly non-authoritative, and contains no product mutation action. | 02. Verify operator audit journey end to end: synthetic scenarios show required metadata and no forbidden content/write capability. | Integrated audit journey acceptance closes only after `TASK-5-1-02` passes persistence, provider-emission, and query-API acceptance.
  - Verification: 01. Build accessible read-only operator audit experience: type-check, adapter/component, keyboard, and accessibility tests. | 02. Verify operator audit journey end to end: approved Playwright/provider-fake/canary command and evidence. | Run the audit journey after `TASK-5-1-02` passes persistence, provider-emission, and query-API acceptance.

## Dependency and concurrency map
- `TASK-5-1-01` depends on `none`.
- `TASK-5-1-02` depends on `TASK-5-1-01`.
- `TASK-5-1-03` depends on `TASK-5-1-01`; the review interface can proceed alongside `TASK-5-1-02`, while integrated audit query/denial verification and task closure wait for `TASK-5-1-02` acceptance.
