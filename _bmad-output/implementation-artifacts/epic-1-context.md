# Epic 1 Context: Create and Manage a Trusted CV

<!-- Compiled from planning artifacts. Edit freely. Regenerate with compile-epic-context if planning docs change. -->

## Goal

Establish CareerFitCV's trusted user boundary and structured CV source. Users can create and access a private account, maintain a mutable CV Profile, and preserve named immutable CV Versions whose exact source remains reliable for later matching, Preview, and Export.

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

Support registration, sign-in, sign-out, current-account retrieval, expiry recovery, and server-enforced ownership for every Profile and Version operation. Authentication failures and cross-user resource access must be non-disclosing. Passwords, hashes, cookies, CSRF material, and internal security metadata must never be returned, stored in browser state, or written to ordinary logs.

The Profile is one mutable structured aggregate covering personal information, summary, skills, education, experience, projects, certificates, languages, and activities. Writes are atomic; invalid nested data leaves the previous state unchanged; optional sections may be empty; repeatable items have stable server-owned IDs. A Version is a complete named snapshot of an owned Profile, immutable after creation, and unaffected by later Profile edits. All writes expose clear success, retryable failure, or terminal failure states.

Laravel is authoritative for validation, identity, authorization, persistence, and state transitions; frontend validation only improves interaction. PostgreSQL 16 is the canonical integration and production database. Verification requires PHP/Laravel tests, Vitest checks, and Playwright critical journeys against disposable data, with shared contract fixtures.

## Technical Decisions

First-party web authentication uses Sanctum stateful HttpOnly cookie sessions with CSRF protection. Registration and sign-in regenerate the session; logout invalidates it. Product routes live under `/api/v1`; successful resources use a `data` envelope, paginated collections add `meta` and `links`, and failures use machine-readable `code`, `message`, and field-keyed `details`. Sensitive responses are private/no-store where appropriate.

Existing Laravel User/system identifiers retain their types; new Profile, item, and Version aggregates use server-generated ULIDs. Ownership is resolved from the authenticated User, never from a client-supplied owner ID. Profile writes and Version creation use explicit transactions and database constraints. Profile updates use the approved optimistic-concurrency `If-Match` contract. Version reads use the stored versioned snapshot and never reconstruct history from mutable Profile data. The approved Profile and Version contracts are the single shared schema source for all dependent stories.

## UX & Interaction Patterns

Account and Profile writes expose idle, dirty, submitting, success, validation, authorization/not-found, retryable, terminal, and stale-conflict states where relevant. Pending writes prevent duplicate submission; ambiguous results are reconciled before retry. Field errors are programmatically associated with controls and failed submissions provide a focusable error summary. Valid non-sensitive input is preserved after validation failure, credentials are cleared, and session expiry clears protected data before redirecting to sign-in. Empty optional sections are explicit empty states, not failures.

## Cross-Story Dependencies

Story 1.1 establishes the User, session, current-account, error, and fixture boundary consumed by Story 1.2 and later protected stories. Story 1.3 establishes the owned mutable Profile aggregate and shared editor boundary; Stories 1.4–1.7 depend on that schema and can proceed in parallel when their shared fixtures and persistence coordination are ready. Story 1.8 depends on the complete Profile schema and creates immutable Version snapshots; its regression checks integrate with all section stories.

Shared Vitest/Playwright enablement, reusable fixtures, CI commands, and disposable PostgreSQL orchestration are project-level work delivered once for the Epic. No story may fork local User, Profile, Version, or error contracts.
