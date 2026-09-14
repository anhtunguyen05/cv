# Story 4.6: Regenerate a rejected or invalid Patch — Requirements

Return to [Story overview](README.md); shared [rules](../../business-rules.md),
[data/lifecycle](../../data-and-lifecycle.md), [security](../../security-and-access.md),
and [test strategy](../../test-strategy.md) remain authoritative.

<frozen-after-approval reason="human-owned behavior — do not modify unless human renegotiates">

**Always:** Authorize full predecessor/source/Evidence graph; require rejected or
approved later-invalidated application Patch state, never a malformed provider
candidate; revalidate context before provider call and persistence;
reuse the controlled provider/Patch validator; link a new lineage item; preserve
predecessor/source; reconcile duplicates and failures safely.

**Never:** Reopen/overwrite predecessor; regenerate pending/applied/foreign
Patch; reuse stale or deleted Evidence contrary to policy; bypass quality/schema
validation; auto-apply; introduce a second provider/orchestrator path; hide why
context is unavailable.

| Scenario | Expected behavior |
| --- | --- |
| Eligible rejected/invalid predecessor | New separately identified validated pending proposal linked to predecessor |
| Provider returns invalid or fails | No misleading pending result; predecessor remains historical; safe retry/terminal state |
| Evidence/session/source context stale/unavailable | No provider call/result; explain next action |
| Duplicate/concurrent/lost regeneration | One approved lineage attempt/result under idempotency policy |
| Predecessor becomes applied or otherwise changes | Conflict and current allowed actions; no generation |
| Foreign/missing/mixed source graph | Non-disclosing failure and no data/provider disclosure |

</frozen-after-approval>

## Concern coverage

Backend owns eligibility, context validation, lineage/idempotency, provider reuse,
Patch validation/persistence, and transaction. Security owns ownership, minimum
disclosure, injection/secrets/logging. Frontend owns reason/context summary,
confirm/request/progress/stale/failure/retry/new-proposal navigation. Integration
and verification prove immutable predecessor/source and one lineage result.
