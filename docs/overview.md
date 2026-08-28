# CareerFit AI Documentation Map

CareerFit AI is a CV and Job Description matching product for students and job seekers. The README describes the target product vision; the codebase currently contains a Vue 3 frontend scaffold and a Laravel backend scaffold, with only a thin health endpoint implemented on the API side.

## What Exists Now

- `apps/web`: Vue 3 + Vite + Pinia + Vue Router starter app
- `apps/api`: Laravel application skeleton
- `docs/`: architecture, API, database, and AI workflow notes
- No domain models for CVs, job descriptions, patches, or match reports yet

## Reading Order

1. `architecture.md`
2. `database.md`
3. `api.md`
4. `ai-workflow.md`
5. `decisions.md`

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
