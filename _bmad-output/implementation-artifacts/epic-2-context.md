# Epic 2 Context: Understand CV Fit for a Job

<!-- Compiled from planning artifacts. Edit freely. Regenerate with compile-epic-context if planning docs change. -->

## Goal

Epic 2 lets an authenticated User preserve a target Job Description, inspect a
deterministic interpretation of its requirements, compare one immutable CV
Version against the current Job Description revision, and review an explainable
Match Report. The feature must preserve source identity and historical meaning
without an LLM, provider, worker, recruiter ranking, or unsupported claim.

## Stories

- Story 2.1: Save a Job Description
- Story 2.2: Manage saved Job Descriptions
- Story 2.3: Analyze a Job Description
- Story 2.4: Generate a Match Report
- Story 2.5: Review an explainable Match Report

## Requirements & Constraints

- Every resource is authenticated-user-owned and non-disclosing for absent or
  foreign identifiers. Laravel is authoritative for validation, authorization,
  persistence, and transitions; Vue owns interaction state only.
- A Job Description has a stable ULID and immutable revisions. Raw source is
  preserved after transport decoding. Updates create one new current revision;
  logical deletion blocks new work but preserves pinned history.
- Analysis is deterministic and pinned to one revision and rule version. Match
  Reports are immutable and pin the CV Version, Job Description, revision,
  Analysis, and matching rule version.
- `/api/v1` uses the common `{data}` success envelope and structured errors.
  PostgreSQL 16 is the integration datastore; SQLite cannot prove migration or
  constraint behavior. Sensitive CV/JD content never enters ordinary logs.
- Every user-facing surface defines loading, empty, validation, authorization,
  retryable failure, terminal failure, reload, and keyboard-accessible states.

## Technical Decisions

- Product routes are `/api/v1/job-descriptions` and
  `/api/v1/match-reports`, with nested synchronous analysis. Resource IDs are
  ULIDs; active lists use page-number pagination, default 20/max 100.
- JD source validation is Unicode-aware with a 50,000-code-point/200 KiB raw
  limit and bounded optional company/role metadata. `If-Match` protects update
  and delete; UUID-v4 `Idempotency-Key` protects mutations and derived work.
- Analysis schema/rule versions start at `1.0.0`; signal states are
  `detected`, `absent`, or `unknown`, using a closed deterministic vocabulary.
  Derived work has a five-second synchronous deadline and no partial success.
- Match rule `1.0.0` scores applicable categories with required/preferred,
  evidence, seniority, and role/domain weights. Strong evidence comes from CV
  project/experience content; skills-only or summary-only evidence is Weak.
  Reports provide source references and advisory recommendations, never ATS or
  score-boost claims.
- Shared decisions are recorded as proposed until Pc approves them. The early
  `E2-COORD-JD-DELETE-001` checkpoint lets report-read work consume deletion
  backend evidence without creating a 2.2/2.5 dependency cycle.

## UX & Interaction Patterns

The JD flow supports create, reload, list, edit, stale-conflict recovery,
logical-delete confirmation, and clear source/revision labels. Analysis and
matching show deterministic rule versions and explicit retryable/terminal
states. Reports use semantic Matched, Missing, and Weak Evidence groups with
keyboard navigation, associated validation messages, useful focus movement,
and no color-only score meaning. Deleted-source context is shown only on an
owner-readable historical report.

## Cross-Story Dependencies

Story 2.1 establishes the Job Description aggregate and revision checkpoint.
Story 2.2 consumes it for revisions/deletion and publishes the early deletion
checkpoint. Story 2.3 consumes immutable revisions and publishes Analysis.
Story 2.4 requires the approved Epic 1 immutable CV Version contract plus a
successful current Analysis. Story 2.5 consumes the stored Match Report and
must not recompute it. All stories share one fixture, PostgreSQL, and
Playwright integration owner.

## Verification evidence (2026-10-07)

- The local API environment was advanced with the two pending forward
  migrations. The registration and Trusted CV prerequisite journeys now pass;
  the browser API uses the current `normalized_title`, `revision`, and
  `document` CV schema.
- `apps/web/tests/e2e/epic2-job-fit.spec.ts` covers registration, CV Version
  creation, JD save/reload, current-revision Analysis, Match Report creation,
  report navigation, and report reload. `npm run test:e2e` passes 3/3 tests.
- `analysis-v1.json` contains 40 reviewed cases (20 English and 20
  Vietnamese/mixed-language) plus 12 held-out counterexamples. The PHPUnit
  corpus runner executes every case twice and asserts canonical repeatability,
  signal states, aliases, negation, Unicode, and unknown/absent behavior.
- `match-report-v1.json` contains 40 reviewed evaluation cases plus 12 held-out
  counterexamples. The PostgreSQL/SQLite runner executes all cases through the
  deterministic Match service, asserts classifications, score, references,
  recommendations, source identity, and canonical replay.
- `job-description-v1.json` is consumed by an API contract test for its valid
  and validation cases. Full API verification passes on SQLite (58 tests,
  57 passed, 1 skipped, 1,960 assertions) and disposable PostgreSQL 16 (58
  tests, 1,962 assertions); the expanded match corpus and boundary tests cover
  replay, ownership, throttling, corruption, pagination, and CV snapshot
  reproducibility.

The Playwright and quality-corpus gates are complete. `sprint-status.yaml` now
places all five Epic 2 stories in `review`. Database/model-level immutability
guards and pre-existing ATS/AI chrome copy remain explicitly deferred; browser
checks provide automated keyboard/focus and accessibility-state evidence, while
a human screen-reader sign-off remains an optional release checkpoint.
