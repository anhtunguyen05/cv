# AI Workflow

## 1. Workflow Goal

The AI layer should help a user improve a CV for one specific job description without inventing experience or changing the CV behind the user's back.

## 2. Current State

The repository does not yet implement the AI workflow. The README describes the intended system behavior, and this document turns that into an explicit operating model.

## 3. Core Rules

- never fabricate experience, employers, or metrics
- ask for missing evidence before rewriting weak sections
- generate patches, not silent overwrites
- require human approval before applying a patch
- record tool calls for auditability

## 4. End-to-End Flow

```text
1. User creates or imports CV data
2. User pastes a job description
3. System analyzes the JD
4. System compares CV vs JD
5. System generates a match report
6. System detects weak or missing evidence
7. AI asks follow-up questions
8. User answers with concrete evidence
9. AI drafts a structured patch
10. Backend validates the patch
11. User accepts, rejects, or edits it
12. Approved patch becomes a new CV version
13. User exports the final CV
```

## 5. Patch Lifecycle

### Draft

The AI proposes a change with section, field, old value, new value, and reason.

### Validate

The backend checks:

- schema shape
- permitted fields
- evidence presence
- safe operation type

### Review

The user inspects the change before it is committed.

### Apply

Once approved, the patch creates or updates a versioned CV record.

## 6. Why Patch-Based AI Matters

Patch-based output is safer than direct rewriting because it:

- keeps changes reviewable
- preserves original evidence
- supports rollback
- makes disagreement visible
- allows targeted regeneration

## 7. Failure Modes

- weak evidence in the original CV
- user cannot answer the follow-up questions
- AI tries to overstate experience
- patch validation rejects unsupported fields
- export fails after a patch is approved

## 8. Operational Notes

- store tool-call metadata
- keep prompt templates versioned
- log model and provider used for each session
- separate analysis from application

## 9. Future Extension

The multi-agent model in the README can be added later, but the first version should keep orchestration centralized so behavior stays easier to debug.
