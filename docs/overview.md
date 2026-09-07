# CareerFit AI Documentation Map

CareerFit AI is a CV and Job Description matching product for students and job seekers. The README describes the target product vision; the codebase currently contains a Vue 3 frontend scaffold and a Laravel backend scaffold, with only a thin health endpoint implemented on the API side.

## What Exists Now

- `apps/web`: Vue 3 + Vite + Pinia + Vue Router starter app
- `apps/api`: Laravel application skeleton
- `docs/`: architecture, API, database, and AI workflow notes
- No domain models for CVs, job descriptions, patches, or match reports yet

## Reading Order

1. `../_bmad-output/planning-artifacts/epics.md` — canonical epics, stories, and acceptance criteria
2. `story-execution.md` — current BMAD story-to-task breakdown protocol
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

Current planning and task breakdown are managed through BMAD artifacts under
`_bmad-output/`. `implementation-plan.md` and `implementation/` are retained as
legacy references and are not current planning authority.
