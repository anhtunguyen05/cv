# Architecture

## 1. System Intent

CareerFit AI is designed to help a user adapt a structured CV to a specific job description. The key design choice is to treat the CV as structured data, not free text, so the system can compare, revise, version, and export it safely.

## 2. Current Implementation Snapshot

The repository is still at an early stage.

- Frontend: Vue 3, Vite, Pinia, Vue Router
- Backend: Laravel 13 skeleton
- API surface: `GET /health`
- Web surface: default Vue starter routes and views
- Persistence: default Laravel tables plus SQLite development database

In other words, the current codebase provides the application shell, not the product logic yet.

## 3. Target Architecture

```text
[Vue Web App]
  - CV builder
  - JD input
  - match report
  - interview chat
  - patch review
  - template preview
  - export

        |
        v

[Laravel API]
  - auth
  - CV profile module
  - JD analysis module
  - matching module
  - AI orchestrator
  - patch module
  - versioning module
  - export module

        |
        v

[AI / Worker Boundary]
  - controlled tool calls
  - schema validation
  - audit logging
  - async export or parsing jobs
```

## 4. Component Responsibilities

### Web App

The web app should own the interactive user experience:

- profile editing
- job description entry
- match result review
- interview prompts and answers
- patch approval
- template preview

### API

The API should own all trusted state transitions:

- authentication
- persistence
- matching logic
- patch validation
- version creation
- export orchestration

### AI Layer

The AI layer should not write directly to storage. It should:

- inspect structured inputs
- generate a recommendation or patch proposal
- validate output against schema
- return evidence and rationale

### Worker Layer

The worker layer is optional but useful for expensive tasks:

- PDF generation
- document parsing
- queued analysis jobs
- background exports

## 5. Why This Shape

### Structured JSON over Free Text

The README correctly pushes the product toward structured CV data. That makes matching and patching much more reliable than editing prose blobs.

### Patch-based Editing over Direct Overwrite

AI output is easier to review when it is expressed as a patch. This keeps the user in control and gives the system a clear audit trail.

### Single Orchestrator First

The multi-agent model in the README is best treated as a future extension. The initial system should stay simple and use one orchestrator with explicit tools.

## 6. Main Gaps

- no CV domain tables yet
- no JD analysis endpoints yet
- no AI interview session flow yet
- no template engine yet
- no export pipeline yet

## 7. Architectural Principle

The product should favor correctness, traceability, and user approval over aggressive automation.
