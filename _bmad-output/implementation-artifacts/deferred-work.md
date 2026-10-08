## Deferred from: code review of spec-epic-2-job-fit (2026-10-07)

- Shared ATS/AI chrome copy is pre-existing outside the Epic 2 feature surface; product-owner scope decision is recorded in the Epic 2 spec before changing it.
- Database/model-level immutability guards for revisions, analyses, and Match Reports are deferred; Epic 2 relies on the application-level no-mutation API surface.

- source_spec: `_bmad-output/implementation-artifacts/spec-5-5-validate-deterministic-matching-quality.md`
  summary: Run repeatability in independent processes or approved isolated environments.
  evidence: The local evaluator performs two deterministic in-process runs with reversed case order; independent environment evidence remains blocked by E5-DEC-008 and canonical Story 5.5 verification tasks.
- source_spec: `_bmad-output/implementation-artifacts/spec-5-5-validate-deterministic-matching-quality.md`
  summary: Add a prohibited-content and synthetic-provenance canary scan for quality fixtures.
  evidence: The foundation validates schema, versions, hashes, and approved IDs, while the full Epic test strategy still requires privacy/content canaries under the approved environment gate.
- source_spec: `_bmad-output/implementation-artifacts/spec-5-5-validate-deterministic-matching-quality.md`
  summary: Implement baseline waiver, owner, expiry, rollback, and triage evidence.
  evidence: Seven-day waiver semantics belong to canonical Story 5.5 tasks 5-5-02 and 5-5-06 and require the named quality owner; no waiver is accepted by this local gate.
- source_spec: `_bmad-output/implementation-artifacts/spec-5-5-validate-deterministic-matching-quality.md`
  summary: Publish immutable CI evaluation artifacts with access and retention controls.
  evidence: CI publication is canonical task 5-5-05 and is intentionally blocked until E5-DEC-008 defines the disposable environment and artifact policy.
- source_spec: `_bmad-output/implementation-artifacts/spec-5-6-orchestration-measurement-plan.md`
  summary: Integrate the orchestration-plan validator into the full Epic 5 readiness manifest and CI evidence publication.
  evidence: Story 5.6 plan validation intentionally does not execute measurement or close Story 5.7; integration requires approved E5-DEC-007/E5-DEC-008 and completion evidence from the remaining operational-safety stories.

- source_spec: `_bmad-output/implementation-artifacts/spec-5-1-audit-redaction-boundary.md`
  summary: Trigger API CI when operational-safety contract and fixture files change.
  evidence: Review found `.github/workflows/ci-api.yml` path filters omit `docs/contracts/operational-safety/**`, so default safety validators can be bypassed by fixture-only changes.
- source_spec: `_bmad-output/implementation-artifacts/spec-5-5-validate-deterministic-matching-quality.md`
  summary: Expand evaluator no-write verification to cover every product persistence surface.
  evidence: Current tests observe only User and MatchReport counts; writes to CV, Job Description, Evidence, Patch, or related tables could remain undetected.
- source_spec: `_bmad-output/implementation-artifacts/spec-5-7-operational-safety-manifest-validator.md`
  summary: Harden baseline and orchestration validators against malformed collections, path escapes, and unassigned owners.
  evidence: Independent review identified pre-existing validation gaps outside the pure Story 5.1 redaction boundary; they require a dedicated validator hardening slice.

- source_spec: `_bmad-output/implementation-artifacts/spec-5-2-provider-failure-policy.md`
  summary: Integrate the bounded provider-failure classifier into the approved provider and asynchronous lifecycle.
  evidence: This slice is intentionally dormant; E5-PREREQ-AI-001 and E5-DEC-002/003/005/008 remain unresolved, so PatchService, provider, queue, retry-budget, cancellation, persistence, audit, telemetry, and end-to-end acceptance cannot be claimed.

- source_spec: `_bmad-output/implementation-artifacts/spec-5-1-audit-redaction-boundary.md`
  summary: Add a public operator audit API, RBAC surface, export flow, and provider-emission integration.
  evidence: The self-directed MVP ships append-only storage and bounded query services only; E5-DEC-001 keeps operator HTTP/export surfaces disabled.
- source_spec: `_bmad-output/implementation-artifacts/spec-5-2-provider-failure-policy.md`
  summary: Add asynchronous queue/worker lifecycle, lease races, late-result reconciliation, and user recovery UI.
  evidence: E5-DEC-003 explicitly defers generic async work; the MVP implements synchronous classifier/retry bounds only.
- source_spec: `_bmad-output/implementation-artifacts/spec-5-3-retention-disposable-safety.md`
  summary: Add persistent deletion requests, scheduler, external/backup adapters, and destructive production execution.
  evidence: E5-DEC-004 limits this delivery to count-only dry-run previews and disposable checkpoints.
- source_spec: `_bmad-output/implementation-artifacts/spec-5-4-telemetry-safety.md`
  summary: Add production telemetry vendor, dashboards, alert routing, SLO drills, and operator views.
  evidence: E5-DEC-005 limits this delivery to a pure local metric boundary with no vendor or credentials.
- source_spec: `_bmad-output/implementation-artifacts/spec-5-6-orchestration-measurement-plan.md`
  summary: Execute provider-backed measurements and adopt multi-agent runtime components.
  evidence: E5-DEC-007 records an explicit defer verdict; no production provider or multi-agent component is authorized.
- source_spec: `_bmad-output/implementation-artifacts/spec-5-7-operational-safety-manifest-validator.md`
  summary: Replace the disposable pass-with-gaps verdict with production readiness approval.
  evidence: E5-DEC-009 requires a new evidence run after production topology, operator, vendor, and destructive-policy approvals.

- source_spec: `_bmad-output/implementation-artifacts/spec-5-2-provider-recovery-mvp.md`
  summary: Supersede the earlier dormant-classifier note for synchronous Patch recovery.
  evidence: E5-DEC-002/E5-DEC-003 are now self-approved for the disposable MVP; PatchService integrates bounded synchronous retry classification and sanitized audit emission, while only the async branch remains deferred.
