# GitHub Issues — Phase 5

Suggested labels: `phase-5`, `security`, `observability`, `ai`, `operations`.

## #P5-1 — Add sanitized AI tool-call audit records

**Depends on:** Phase 4  
**Labels:** `phase-5`, `ai`, `security`, `backend`

- [ ] Add `ai_tool_calls` migration/model and retention metadata.
- [ ] Record tool/provider/model/status/duration without secrets.
- [ ] Add redaction tests and ownership/access rules.

**Acceptance:** an operator can trace a call outcome without raw sensitive
content being logged by default.

## #P5-2 — Harden provider and background failure handling

**Depends on:** `#P5-1`  
**Labels:** `phase-5`, `ai`, `operations`

- [ ] Add timeout, bounded retry, rate-limit, and malformed-response handling.
- [ ] Add queue failure/retry behavior where asynchronous work exists.
- [ ] Expose actionable user-facing failure states.
- [ ] Add fake-provider failure tests.

**Acceptance:** provider failures do not create unvalidated patches or stuck
export records.

## #P5-3 — Add retention, deletion, and safety controls

**Depends on:** `#P5-1`  
**Labels:** `phase-5`, `security`, `privacy`

- [ ] Document retention for messages, tool calls, and exports.
- [ ] Implement user data deletion and related cleanup.
- [ ] Add evidence provenance and approval-history checks.
- [ ] Verify secrets remain environment/secret-store managed.

**Acceptance:** deletion behavior is testable and documented before production.

## #P5-4 — Add operational metrics and review validators

**Depends on:** `#P5-2`, `#P5-3`  
**Labels:** `phase-5`, `observability`, `ai`

- [ ] Measure latency, failure rate, patch rejection, and export status.
- [ ] Add deterministic ATS/review validators.
- [ ] Document dashboards or repeatable operator queries.

**Acceptance:** common failures can be identified from metrics and sanitized
logs without reproducing with user content.

## #P5-5 — Decide whether multi-agent expansion is justified

**Depends on:** `#P5-4`  
**Labels:** `phase-5`, `architecture`

- [ ] Measure single-orchestrator limitations.
- [ ] Record an ADR if splitting agents is justified.
- [ ] Preserve tool schema, validation, and human approval boundaries.
- [ ] Otherwise record the decision to defer multi-agent work.

**Acceptance:** no agent is added solely because the target architecture lists
one; the decision is evidence-based.

## #P5-6 — Phase 5 checkpoint

**Depends on:** `#P5-2`, `#P5-3`, `#P5-4`, `#P5-5`  
**Labels:** `phase-5`, `testing`

- [ ] Run all application checks and failure-path tests.
- [ ] Perform a redaction/deletion smoke test.
- [ ] Update status and operational documentation.

**Acceptance:** all phase exit criteria pass.
