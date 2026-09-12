# Story 2.3: Analyze a Job Description — Requirements

Return to the [Story overview](README.md) and Epic [business rules](../../business-rules.md),
[contracts](../../contracts.md), [data/lifecycle](../../data-and-lifecycle.md),
and [test strategy](../../test-strategy.md).

<frozen-after-approval reason="human-owned behavior — do not modify unless human renegotiates">

## Boundaries and constraints

**Always:** Authorize the active logical JD and current revision; read its raw
text without mutation; run the approved deterministic rule version; validate
the complete output schema; pin exact source/version; expose missing signals
explicitly; preserve saved source on every outcome.

**Ask first:** Approve the analysis vocabulary, schema, normalization aliases,
rule-version ownership, repeated request behavior, and execution limits.

**Never:** Analyze a historical/deleted/foreign source for new work; invent a
signal; overwrite raw input; call an LLM/provider/worker; persist partial output
as success; silently reuse an Analysis from another revision/rule version.

## Story-specific rules and edge cases

- **JD-ANALYSIS-BR-001:** Output ordering and canonicalization are deterministic
  across platform/process runs, not merely within one request.
- **JD-ANALYSIS-BR-002:** Duplicate and overlapping terms follow one approved
  normalization/alias policy while preserving enough source evidence to
  explain extraction.
- **JD-ANALYSIS-BR-003:** If the source becomes non-current or deleted before
  success commits, the request follows the approved conflict policy and cannot
  attach a successful result to the wrong logical state.

| Scenario | Expected behavior |
| --- | --- |
| Current owned revision with known signals | Complete schema, exact revision/rule IDs, stored separately from raw text |
| Missing/ambiguous/unrecognized signal | Explicit absent/unknown result; no fabricated value |
| Repeated same revision + rule | Same normalized result and approved dedupe semantics |
| Parser/schema failure or timeout | No successful partial Analysis; raw source intact; actionable retry/terminal state |
| New revision created during request | Approved conflict/snapshot behavior with no wrong-current success |
| Deleted/foreign/missing JD | No work and non-disclosing failure |

</frozen-after-approval>

## Coverage by concern

| Concern | Authoritative coverage |
| --- | --- |
| Behavior | Signal, missing, repeat, and failure scenarios plus canonical ACs |
| Contract | [contract.md](contract.md) and shared Analysis/error representations |
| Backend | Parser/normalizer, schema validation, application transaction, deterministic persistence |
| Security | Owner/current/deleted guards, sensitive text, local-only processing, safe logs |
| Validation | Output vocabulary/schema/version and source preconditions |
| Frontend | Analyze action, raw/derived labels, loading/success/stale/retry/terminal states |
| Integration | Shared revision/Analysis fixtures and cache/version keys |
| Verification | [verification.md](verification.md), corpus repeatability, and regression gate |
