# Story 2.1: Save a Job Description — Requirements

Return to the [Story overview](README.md). Shared rules remain authoritative in
[Epic business rules](../../business-rules.md), [data and lifecycle](../../data-and-lifecycle.md),
[security and access](../../security-and-access.md), and
[UX and validation](../../ux-and-validation.md).

<frozen-after-approval reason="human-owned behavior — do not modify unless human renegotiates">

## Boundaries and constraints

**Always:** Require the approved session; store decoded raw text exactly and
canonicalize only a separate derived view; atomically create the logical resource
and revision 1; derive ownership server-side; return safe DTOs.

**Ask first:** Resolve the Story's planning blockers and record approval
evidence before implementation.

**Never:** Accept an owner/revision number from the client; analyze during save;
send text to a provider; accept file/URL import; store HTML as trusted markup;
create a root without a revision or a revision without its root.

## Story-specific rules and edge cases

- **JD-SAVE-BR-001:** A successful create has one logical ULID, exactly one
  immutable initial revision, and a current pointer to that revision.
- **JD-SAVE-BR-002:** The stored raw source preserves decoded whitespace and
  newlines exactly; validation uses a separate approved normalization contract
  shared by UI hints and server enforcement.
- **JD-SAVE-BR-003:** Duplicate clicks, transport retries, timeouts after commit,
  and concurrent equivalent requests follow the approved deduplication and
  reconciliation policy without presenting uncertain state as failure/success.

| Scenario | Expected behavior |
| --- | --- |
| Valid text with or without optional metadata | One owned logical JD plus revision 1; returned resource reloads unchanged |
| Empty-after-normalization, oversized, malformed, or invalid encoding | Field/global safe error; no root or revision |
| Unauthenticated or expired session | Global auth/session recovery behavior; no persistence |
| Foreign or missing ID read | Identical non-disclosing not-found state |
| Lost success response | Reconcile through approved ID/list strategy before offering unsafe retry |
| Script/markup-like source text | Stored and rendered as inert text; not executed or logged |

</frozen-after-approval>

## Coverage by concern

| Concern | Authoritative coverage |
| --- | --- |
| Behavior | Rules/scenarios above and canonical ACs in [README](README.md) |
| Contract | Story slice in [contract.md](contract.md) plus Epic/Global envelopes |
| Backend | Aggregate/revision atomicity, policy, service, persistence, and transaction tasks |
| Security | Session, ownership, non-disclosure, sensitive text, safe rendering, and rate limits |
| Validation | Raw text and optional metadata rules plus malformed/transport boundaries |
| Frontend | Create/reload, pending, field/global error, lost success, and accessible state |
| Integration | Shared fixtures, credentials, code/path mapping, and cache/reconciliation rules |
| Verification | AC mapping and evidence gates in [verification.md](verification.md) |
