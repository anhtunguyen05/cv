# Architecture Overview

## Authority and scope

This is the stable overview for implementation. The initiative architecture
spine records the numbered, durable decisions that bind this document. Product
requirements, epic rules, stories, and sprint state remain in `_bmad-output/`.

## Current versus target

The repository currently has a Vue/Vite foundation, a Laravel 13 foundation
with `GET /api/health`, and an optional Python worker health service. CV,
Job Description, matching, preview, export, and AI domain behavior are target
design until implemented and verified.

## System boundary

```text
Vue web client
  -> Laravel /api/v1 boundary
     -> application and domain rules
        -> MySQL trusted state

Optional worker / AI provider
  -> explicit result or proposal contract
     -> Laravel validation and state transition
```

Laravel is the sole owner of authentication, authorization, validation,
persistence, and trusted state transitions. Vue owns presentation and
interaction state. A worker or provider never writes trusted state directly.

## Product-state invariants

- A CV Profile is mutable; a saved CV Version is immutable.
- A Job Description has immutable revisions; analyses and Match Reports pin the
  exact revision and rule versions used.
- MVP matching is deterministic and repeatable for the same inputs and rule
  version.
- Preview and browser Export use a saved CV Version and selected Template, not
  unsaved editor state.
- AI is post-MVP, proposal-only, evidence-bound, and explicitly user-approved.

## Operational posture

MySQL 8.4 is canonical for shared development, integration/E2E verification,
and production. SQLite is local fast-test/scaffold support only. Redis, the
worker, queue processing, and server-side PDF are optional and cannot block the
core MVP flow.

## Deferred decisions

- Production deployment/storage topology.
- Retention and deletion periods for AI messages, tool records, and artifacts.
- Matching evaluation fixtures and quality threshold.
- Template visual direction and print specification.
- A justified trigger for server-side export or multi-agent decomposition.
