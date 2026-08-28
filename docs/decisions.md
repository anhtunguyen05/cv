# Decisions

This document records the main design choices implied by the README and the current codebase.

## 1. Structured CV Data

### Decision

Store CV content as structured data instead of plain text.

### Why

- enables reliable comparison against job requirements
- makes patch generation safer
- supports versioning and rollback

### Trade-off

This is more work than freeform editing, but it avoids fragile text rewriting later.

## 2. Patch-Based AI Output

### Decision

AI should propose patches, not overwrite CV records directly.

### Why

- preserves human review
- makes evidence visible
- keeps the audit trail clear

### Trade-off

The workflow is slightly longer, but the user stays in control.

## 3. Single Orchestrator First

### Decision

Start with one controlled AI orchestrator before splitting into multiple agents.

### Why

- easier to debug
- easier to validate
- less routing complexity

### Trade-off

It is less modular initially, but much safer for an early-stage product.

## 4. Scaffold vs Target State

### Decision

Document the current scaffold state separately from the target product architecture.

### Why

- prevents misleading docs
- makes project progress easier to track
- helps new contributors understand what exists now

### Trade-off

The docs are a little more explicit, but much more honest.

## 5. Open Questions

- which auth flow will be used first
- whether exports happen in-browser or through a worker
- whether CV templates are stored as JSON config or rendered components
- which AI provider is the initial default

