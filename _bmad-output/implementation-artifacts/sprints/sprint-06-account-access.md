---
sprint_id: sprint-06-account-access
title: Account Access
status: draft
start: null
end: null
facilitator: unassigned
goal: Deliver and independently verify the session-based account-access journey for Stories 1.1 and 1.2, including the shared disposable browser-test harness.
refinement_stories:
  - 1-1-register-an-account
  - 1-2-sign-in-and-sign-out
committed_stories: []
capacity_assumptions:
  - One full-stack delivery owner integrates the shared auth boundary and one reviewer validates cross-layer evidence; calendar capacity is not yet committed.
constraints:
  - The charter remains draft until a facilitator, dates, and delivery capacity are confirmed; no Story lifecycle changes are implied.
  - `E1-COORD-TEST-001` is implemented once here and reused by later Epic 1 sprints; it must use the isolated project/port/volume contract in the Epic decision register.
---

# Sprint 06: Account Access

## Outcome and done signal

Deliver the approved Sanctum session lifecycle and the browser journey that
proves registration, sign-in, current-account hydration, sign-out, expiry, and
safe protected-state clearing. This sprint also lands the reusable disposable
verification harness required by all later Epic 1 browser evidence.

The measurable done signal is passing contract, API, component, and disposable
browser evidence for the canonical account journeys; no password, token, or
protected stale state is exposed; and later sprints can invoke the one approved
verification entry point without touching a development database.

## Capacity and dates

Dates remain unset. The integration owner, reviewer availability, and observed
test runtime determine the eventual timebox; this draft makes no velocity claim.

## Entry gates

- The account, navigation, and endpoint decisions are approved in
  `E1-DEC-001`, `E1-DEC-002`, `E1-DEC-007`, and `E1-DEC-009`.
- Before any shared auth file is changed, name one integration owner and
  non-`main` branch/worktree for `E1-COORD-AUTH-001`.
- Before browser evidence is accepted, implement `E1-COORD-TEST-001` exactly
  as specified in the Epic decision register.

## Dependencies and sequence

Sequence the shared harness and auth integration boundary first, then complete
the account journey against the approved contract. The Story 1.2 journey
consumes the same session and current-account boundary; no local alternative
is permitted.

## Delivery evidence gates

1. The shared harness assigns each run a disposable project, database, ports,
   and browser storage state. Its global setup creates unique accounts through
   the public registration journey (or a versioned, explicitly approved fixture
   API); CSRF/session bootstrap remains an asserted behavior, not a hidden helper.
2. Expiry evidence uses a deterministic test-only session lifetime or clock
   control, then proves `401`/`419` clears all protected client state.
3. Contract/API evidence compares unknown-email and wrong-password outcomes,
   validates duplicate and throttle handling, proves session-ID regeneration,
   and scans responses, logs, fixtures, and browser state for credential/token
   leakage.

All membership is recorded in this charter frontmatter. No Story lifecycle or task lifecycle is copied here. An external issue tracker may mirror permanent task keys under the mapping and verification rules in this directory README.
