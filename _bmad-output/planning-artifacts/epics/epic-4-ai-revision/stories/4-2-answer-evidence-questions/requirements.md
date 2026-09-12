# Story 4.2: Answer Evidence questions — Requirements

Return to [Story overview](README.md); shared [rules](../../business-rules.md),
[security](../../security-and-access.md), and [UX/validation](../../ux-and-validation.md)
remain authoritative.

<frozen-after-approval reason="human-owned behavior — do not modify unless human renegotiates">

**Always:** Authorize active session and exact question; require exactly one
answer or cannot-provide outcome; preserve decoded original text and User
provenance; validate server-side; reconcile safe duplicates; advance session
only through the approved state transition.

**Never:** Treat blank/unknown as positive Evidence; overwrite provenance;
accept a client owner/source/timestamp; answer a stale/foreign/closed question;
send Evidence to a provider in this Story; store unsafe content in logs.

| Scenario | Expected behavior |
| --- | --- |
| Valid text answer | One attributable answer for exact question/session/time |
| Cannot provide/did not perform | Explicit non-supporting outcome, not a claim |
| Empty/oversized/malformed/both modes | Field/global error and no accepted Evidence |
| Stale/out-of-order/closed/expired question | Conflict with input-preserving recovery |
| Duplicate click/lost response/concurrent answer | One reconciled approved outcome |
| Correction/back navigation | Frozen policy preserves original history/provenance |
| Markup-like/Unicode text | Stored as data, rendered inert, excluded from routine logs |

</frozen-after-approval>

## Concern coverage

Behavior/backend cover Evidence and session transitions, persistence, and
transaction. Contract covers answer/read/progress/error. Security covers full
ownership graph, provenance, sensitive data, and safe rendering. Validation
covers raw bounds/mode/question/precondition. Frontend covers draft, submit,
validation, cannot-provide, conflict, progress, reload, and accessibility.
Integration/verification share exact Evidence fixtures and timestamps.
