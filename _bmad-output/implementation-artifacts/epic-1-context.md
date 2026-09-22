# Epic 1 Context: Create and Manage a Trusted CV

<!-- Compiled from planning artifacts. Edit freely. Regenerate with compile-epic-context if planning docs change. -->

## Goal

Establish the trusted user boundary and structured CV source for CareerFitCV. Users must be able to create and access a private account, maintain a mutable structured CV Profile, and preserve named immutable CV Versions whose exact source remains reliable for later matching, Preview, and Export.

## Stories

- Story 1.1: Register an account
- Story 1.2: Sign in and sign out
- Story 1.3: Create a CV Profile
- Story 1.4: Manage CV summary and skills
- Story 1.5: Manage education and experience
- Story 1.6: Manage projects
- Story 1.7: Manage supplementary CV sections
- Story 1.8: Create and view an immutable CV Version

## Requirements & Constraints

The system must support account registration, sign-in, sign-out, current-account retrieval, session-expiry recovery, and server-enforced ownership for every Profile and Version operation. Passwords, hashes, cookies, CSRF material, and internal security metadata must never be returned, persisted in browser storage, or written to ordinary logs. Duplicate registration must not disclose an existing User; invalid writes must return field-keyed errors and leave no partial trusted state.

The CV Profile is a mutable structured aggregate covering personal information, summary, skills, education, experience, projects, certificates, languages, and activities. Profile writes are atomic, optional sections may be empty, and repeatable items have stable server-owned identities. A CV Version is a complete named snapshot, created from an owned Profile, immutable after creation, and independent of later Profile edits. Ownership failures must be non-disclosing.

Laravel is authoritative for validation, identity, authorization, persistence, and state transitions; frontend validation only improves interaction. PostgreSQL 16 is the canonical integration and production datastore. Critical behavior requires API/feature tests, Vitest component/composable coverage, and Playwright browser journeys against disposable data.

## Technical Decisions

First-party web authentication uses same-origin Sanctum stateful HttpOnly cookie sessions with CSRF protection. Development uses a Vite proxy for `/api/v1` and `/sanctum`; production uses one origin. Registration and sign-in regenerate the session, logout invalidates it, and protected responses are private/no-store where applicable. Product endpoints use `/api/v1`, success payloads use a `data` envelope, and failures use machine-readable `code`, `message`, and optional field-keyed `details`.

Public Users expose only string-serialized existing User IDs, name, and canonical email. Registration is guest-only, uses the approved 12–72 character password policy, and applies separate Redis-backed IP and email limits. A lost registration response must reconcile through the current-account endpoint before one explicit retry is offered; no automatic duplicate submission is allowed. A 401 or 419 clears protected client state and redirects to login with only a safe internal return path.

New product aggregates use ULIDs; existing Laravel User and system identifiers retain their established types. Profile and Version ownership is resolved from the authenticated User, never from a client-supplied owner ID. Profile and Version persistence uses explicit transactions and database constraints. Version reads use the stored snapshot rather than reconstructing history from mutable Profile data. Open Profile schema, field-limit, concurrency, and Version snapshot decisions must be frozen before their dependent stories are ready for development.

## UX & Interaction Patterns

Account and Profile writes expose idle, dirty, submitting, success, validation, authorization/not-found, retryable, terminal, and stale-conflict states where relevant. Pending writes disable duplicate submission while retaining understandable keyboard focus and progress. Field errors are programmatically associated with controls; failed submissions provide a focusable error summary. Valid non-sensitive input is preserved after validation failure, while passwords and other credentials are cleared. Ambiguous results are reconciled before retry, and session expiry clears protected data before redirecting to sign-in. Empty optional Profile sections are explicit empty states, not failures.

## Cross-Story Dependencies

Story 1.1 establishes the shared User, session, current-account, error, and contract-fixture boundary consumed by Story 1.2 and all later protected stories. Story 1.3 establishes the owned mutable Profile aggregate and editor boundary; Stories 1.4–1.7 depend on its approved schema and may proceed in parallel by non-overlapping sections. Story 1.8 depends on the complete Profile schema and owns immutable Version creation and reads, while its regression checks integrate with the section stories.

Shared Vitest/Playwright enablement, reusable fixtures, CI commands, and disposable PostgreSQL orchestration must be delivered once for all Epic 1 stories. Profile schema, field semantics, write/concurrency behavior, and Version snapshot decisions remain coordination gates rather than local story assumptions.
