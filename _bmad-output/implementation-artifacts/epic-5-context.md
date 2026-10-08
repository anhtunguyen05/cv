# Epic 5 Context: Operate CareerFitCV Safely at Scale

<!-- Compiled from planning artifacts on 2026-10-08. Edit freely. Regenerate with compile-epic-context if planning docs change. -->

## Goal

Establish evidence-backed operational controls for CareerFitCV as provider and
asynchronous capabilities are introduced: sanitized traceability, honest
failure states, retention and deletion, privacy-safe telemetry, deterministic
quality validation, architecture restraint, and a reproducible safety baseline.
Planning artifacts do not authorize production provider traffic, operator
access, destructive deletion, infrastructure, monitoring, or multi-agent
components by themselves.

## Stories

- Story 5.1: Audit AI tool calls safely
- Story 5.2: Handle provider and background failures
- Story 5.3: Retain and delete User data safely
- Story 5.4: Monitor operational health
- Story 5.5: Validate deterministic matching quality
- Story 5.6: Decide whether multi-agent orchestration is justified
- Story 5.7: Verify the operational safety baseline

## Requirements & Constraints

- Operational metadata is non-authoritative and cannot modify CV, JD, Analysis,
  Match Report, Evidence, Patch, Export, or job state.
- Audit, logs, metrics, traces, dashboards, alerts, and evidence must exclude
  credentials and raw CV/JD/Evidence/prompt/provider content; labels and queries
  are bounded and allowlisted.
- Provider failures have stable retryable/terminal categories, bounded retry and
  cost, and never create false success, partial trusted state, or unvalidated
  Patch results. Async work is conditional on a named approved operation.
- Retention/deletion is scoped, dry-runnable, idempotent, resumable, isolated
  between Users, auditable, recoverable, and governed by approved policy.
- Matching evaluation pins fixtures, source, engine/rule/schema/metric/tool
  versions, detects repeatability and counter-metric regressions, and never
  mutates product state.
- Multi-agent adoption requires a reproducible measured limitation of the
  single orchestrator, alternative analysis, preserved boundaries, rollback,
  owner, and approved ADR; insufficient evidence means defer with no runtime
  component.
- Baseline evidence pins source/config/environment/commands/artifacts and
  preserves failures, gaps, unresolved decisions, waivers, freshness, and
  supersession.

## Technical Decisions

- Decisions E5-DEC-001 through E5-DEC-005 and E5-DEC-007 through E5-DEC-009
  are approved for the self-directed disposable MVP on 2026-10-08. They do not
  authorize production provider traffic, operator APIs, destructive deletion,
  vendor monitoring, or generic async infrastructure. E5-DEC-006 remains
  approved by Pc on 2026-10-08 for the deterministic matching quality baseline.
- Use append-only sanitized operational records distinct from trusted product
  persistence. Providers return validated proposal data and never write trusted
  state directly.
- The disposable MVP uses synchronous `patch-proposal` attempts with bounded
  retry candidates; the optional `queued -> running -> ...` lifecycle remains
  disabled until a named async consumer is approved.
- Retention/deletion and baseline evidence are explicit state machines; reruns
  create new immutable artifacts instead of rewriting history.
- Verification uses synthetic fixtures, provider/job fakes, disposable
  PostgreSQL/storage where approved, canary scans, fault injection, and
  independent reruns. No generic queue or production topology is implied.

## UX & Interaction Patterns

- User states distinguish retryable, terminal, cancelled, and in-progress
  outcomes without provider internals or false success.
- Operator views are read-only, bounded, paginated, clearly labeled as
  operational rather than product state, and include loading/empty/error/access
  denied states.
- Retention/deletion communicates scope, consequences, progress, exceptions,
  partial/retryable outcomes, and recovery/support paths without sensitive data.
- Quality, ADR, and baseline evidence identify versions, scope, metrics,
  thresholds, failures, limitations, decisions, owners, and review triggers.

## Cross-Story Dependencies

- Stories 5.1 and 5.2 coordinate shared audit/redaction and failure taxonomy;
  Story 5.4 consumes their stable signals.
- Story 5.3 owns retention/deletion policy and execution evidence.
- Story 5.5 consumes the Epic 2 deterministic Match Report contract and owns
  its synthetic corpus and validator.
- Story 5.6 owns single-orchestrator measurements and the adopt/defer ADR; no
  implementation may start from an unapproved recommendation.
- Story 5.7 consumes evidence from Stories 5.1–5.6 and issues the scoped
  baseline verdict; it must not convert uncommitted or missing evidence into a
  pass.
