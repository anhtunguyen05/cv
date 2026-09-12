# Story 2.4: Generate a Match Report — Requirements

Return to the [Story overview](README.md) and Epic [business rules](../../business-rules.md),
[contracts](../../contracts.md), [data/lifecycle](../../data-and-lifecycle.md),
[security](../../security-and-access.md), and [test strategy](../../test-strategy.md).

<frozen-after-approval reason="human-owned behavior — do not modify unless human renegotiates">

## Boundaries and constraints

**Always:** Authorize both top-level sources; resolve the JD's current active
revision and successful exact Analysis on the server; read a saved immutable CV
Version snapshot; apply one explicit matching-rule version; validate/persist the
complete report and all source IDs atomically.

**Ask first:** Approve scoring, aliases, evidence thresholds, recommendations,
rounding/order, deterministic fixtures, quality threshold, request dedupe, and
source-race behavior.

**Never:** Read live/unsaved Profile state; accept score/classification output
from the client; reuse historical Analysis for current work; match a deleted JD;
mix owners; call an LLM; fabricate claims; persist partial output as success.

## Story-specific rules and edge cases

- **MATCH-GEN-BR-001:** Source resolution and report persistence share an
  approved consistency boundary so a current-revision change cannot silently
  alter the meaning between validation and commit.
- **MATCH-GEN-BR-002:** Deterministic equality covers canonical score,
  classification membership/order, evidence references, and recommendations,
  not only a rounded display value.
- **MATCH-GEN-BR-003:** Repeated/overlapping requests follow an approved policy
  for reuse versus multiple immutable reports; either policy preserves the same
  result and cannot overwrite history.

| Scenario | Expected behavior |
| --- | --- |
| Owned CV Version + current analyzed active JD | Complete immutable report pinned to every required source/rule ID |
| Current revision lacks Analysis | Analyze-first conflict; no report |
| Historical successful Analysis exists only | Not reused; current revision must be analyzed |
| Historical revision or deleted JD requested | New match rejected; old reports/history remain unchanged |
| Foreign/mixed-owner source pair | Non-disclosing denial; no report |
| Same sources/rules repeated | Canonically identical result under approved dedupe policy |
| Source state changes during request | Approved conflict or immutable snapshot resolution; never mismatched IDs/output |
| Engine/schema/storage failure | Retryable/terminal safe error; no partial successful report |

</frozen-after-approval>

## Coverage by concern

| Concern | Authoritative coverage |
| --- | --- |
| Behavior | Source/precondition/determinism scenarios and six canonical ACs |
| Contract | [contract.md](contract.md), shared Analysis/Match/error contracts |
| Backend | Matcher, evidence resolver, source transaction, immutable persistence, dedupe |
| Security | Auth/policies on both sources/report, mixed-owner non-disclosure, safe logs |
| Validation | Identifier shape, current/successful state, rule versions, output schema |
| Frontend | Source selection, analysis-required, pending/conflict/success/failure navigation |
| Integration | Epic 1 Version + Story 2.3 Analysis fixtures and cache invalidation |
| Verification | [verification.md](verification.md), quality corpus, MySQL, and E2E gates |
