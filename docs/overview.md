# CareerFit AI Documentation Map

CareerFit AI is a CV and Job Description matching product for students and job seekers. The README describes the target product vision; the codebase currently contains a Vue 3 frontend scaffold and a Laravel backend scaffold, with only a thin health endpoint implemented on the API side.

## What Exists Now

- `apps/web`: Vue 3 + Vite + Pinia + Vue Router starter app
- `apps/api`: Laravel application skeleton
- `docs/`: architecture, API, database, and AI workflow notes
- No domain models for CVs, job descriptions, patches, or match reports yet

## Reading Order

1. `implementation-plan.md` — executable phases, code locations, tests, and exit criteria
2. `implementation/README.md` — phase folders and GitHub issue execution order
3. `architecture.md` — system boundaries and responsibilities
4. `database.md` — current storage and target domain model
5. `api.md` — current endpoint and proposed resource contracts
6. `ai-workflow.md` — patch lifecycle and human approval rules
7. `decisions.md` — design decisions and unresolved questions

## Core Idea

```text
CV profile + Job Description
    -> match analysis
    -> gap detection
    -> follow-up questions
    -> structured patch
    -> user review
    -> versioned CV export
```

## Documentation Boundary

These docs distinguish between:

- current implementation state in the repository
- target architecture described in the README
- design decisions that keep AI output controlled and auditable

`implementation-plan.md` is the bridge between those descriptions and code. It
is the source for delivery sequencing; it does not claim that a phase is
implemented until its exit criteria and verification checks pass.
