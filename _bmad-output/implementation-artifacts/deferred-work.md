## Deferred from: code review of spec-epic-2-job-fit (2026-10-07)

- source_spec: `_bmad-output/implementation-artifacts/spec-refactor-auth-mutation-boundary.md`
  summary: Refresh documentation and older implementation-artifact links that still reference `features/auth/api/auth.mutations.ts`.
  evidence: The auth mutation module was intentionally relocated to `features/auth/composables/useAuthMutations.ts`; the approved refactor excludes broader documentation and planning-artifact updates.

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

- source_spec: `_bmad-output/implementation-artifacts/spec-fix-prettier-formatting-2.md`
  summary: Reset the CV editor active section when loading a different CV.
  evidence: The formatting-only review found that reset() preserves the previous active section, which can open a new editor on the wrong section.
- source_spec: `_bmad-output/implementation-artifacts/spec-fix-prettier-formatting-2.md`
  summary: Apply the same validation guards to JD match retries as initial report creation.
  evidence: retryMatch() can bypass stale-analysis, unsaved-change, and empty-CV-version guards that createReport() applies.
- source_spec: `_bmad-output/implementation-artifacts/spec-fix-prettier-formatting-2.md`
  summary: Reconcile paginated CV version selection with the currently rendered options.
  evidence: A selected version ID can survive page changes while its option is no longer rendered, leaving the UI and submitted value inconsistent.
- source_spec: `_bmad-output/implementation-artifacts/spec-fix-prettier-formatting-2.md`
  summary: Preserve CV-version pagination errors when cached pages contain multiple pages.
  evidence: The review found the pagination error branch nested under a condition that can hide failures for stale cached data.
- source_spec: `_bmad-output/implementation-artifacts/spec-fix-prettier-formatting-2.md`
  summary: Expose retry handling for retryable server and network failures across JD workflows.
  evidence: Current retry handling excludes several 5xx and network failures, leaving users without recovery controls.
- source_spec: `_bmad-output/implementation-artifacts/spec-fix-prettier-formatting-2.md`
  summary: Prevent concurrent JD save, analyze, and delete actions.
  evidence: Each control checks only its own mutation, so conflicting actions can be started against the same revision.
- source_spec: `_bmad-output/implementation-artifacts/spec-fix-prettier-formatting-2.md`
  summary: Use one consistent rule-version key for cached JD analysis IDs.
  evidence: Analysis IDs are written with a server rule-version key but read and removed with the default key.
- source_spec: `_bmad-output/implementation-artifacts/spec-fix-prettier-formatting-2.md`
  summary: Make dashboard statistics reflect loading, empty, and real data states.
  evidence: Static labels remain visible while counts are zero or loading, producing misleading dashboard content.
- source_spec: `_bmad-output/implementation-artifacts/spec-fix-prettier-formatting-2.md`
  summary: Show dashboard statistic failures instead of silently rendering zero.
  evidence: CV and JD statistic failures currently have no corresponding error state.
- source_spec: `_bmad-output/implementation-artifacts/spec-fix-prettier-formatting-2.md`
  summary: Avoid loading a full Match Report page solely to calculate a dashboard total.
  evidence: The dashboard fetches and exposes a full report collection only to display a count.
- source_spec: `_bmad-output/implementation-artifacts/spec-fix-prettier-formatting-2.md`
  summary: Link the dashboard Match Reports statistic to the report list.
  evidence: The statistic has no direct navigation to the reports it describes.
- source_spec: `_bmad-output/implementation-artifacts/spec-fix-prettier-formatting-2.md`
  summary: Replace nested RouterLink and button markup with one interactive element.
  evidence: Several views wrap Button components in RouterLink, producing invalid nested interactive markup.
- source_spec: `_bmad-output/implementation-artifacts/spec-fix-prettier-formatting-2.md`
  summary: Serialize patch review actions for a single patch revision.
  evidence: Edit, reject, and approve controls only disable their own mutations and can submit competing actions.
- source_spec: `_bmad-output/implementation-artifacts/spec-fix-prettier-formatting-2.md`
  summary: Add an actionable retry or refresh path for patch mutation failures.
  evidence: Patch failures show only a generic refresh message even though retryability is classified.
- source_spec: `_bmad-output/implementation-artifacts/spec-fix-prettier-formatting-2.md`
  summary: Make password strength feedback meaningful and accessible.
  evidence: The current indicator is visual-only, length-based, and labels repetitive passwords as strong.
- source_spec: `_bmad-output/implementation-artifacts/spec-fix-prettier-formatting-2.md`
  summary: Align the registration password input with the schema maximum length.
  evidence: The schema rejects passwords over 72 characters but the input gives no maxlength or explanatory hint.
- source_spec: `_bmad-output/implementation-artifacts/spec-fix-prettier-formatting-2.md`
  summary: Source or qualify quantitative marketing claims.
  evidence: Landing-page claims about score lift and parser compatibility have no visible source or methodology.
- source_spec: `_bmad-output/implementation-artifacts/spec-fix-prettier-formatting-2.md`
  summary: Point the landing-page workflow CTA to the advertised how-it-works section.
  evidence: The CTA text promises an explanation but currently links to registration instead of the local section.
- source_spec: `_bmad-output/implementation-artifacts/spec-fix-prettier-formatting-2.md`
  summary: Align landing-page import messaging with the implemented CV workflow.
  evidence: The page promises CV import while the current UI exposes structured manual entry only.
- source_spec: `_bmad-output/implementation-artifacts/spec-fix-prettier-formatting-2.md`
  summary: Remove or qualify the landing-page score-change estimate.
  evidence: The mockup promises an estimated score increase that conflicts with the Match Report disclaimer.
- source_spec: `_bmad-output/implementation-artifacts/spec-fix-prettier-formatting-2.md`
  summary: Replace product-name text with grounded candidate evidence in the marketing mockup.
  evidence: The mockup presents the product name as evidence, conflicting with the truth-first positioning.

- source_spec: `_bmad-output/implementation-artifacts/spec-5-2-provider-recovery-mvp.md`
  summary: Supersede the earlier dormant-classifier note for synchronous Patch recovery.
  evidence: E5-DEC-002/E5-DEC-003 are now self-approved for the disposable MVP; PatchService integrates bounded synchronous retry classification and sanitized audit emission, while only the async branch remains deferred.
