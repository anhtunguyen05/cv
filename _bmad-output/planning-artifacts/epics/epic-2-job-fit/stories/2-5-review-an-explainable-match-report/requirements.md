# Story 2.5: Review an explainable Match Report — Requirements

Return to the [Story overview](README.md) and Epic [contracts](../../contracts.md),
[security](../../security-and-access.md), [UX/validation](../../ux-and-validation.md),
and [test strategy](../../test-strategy.md).

<frozen-after-approval reason="human-owned behavior — do not modify unless human renegotiates">

## Boundaries and constraints

**Always:** Authorize the report; read stored immutable result and pinned source
summaries; preserve canonical classification/score values; distinguish groups
semantically; show deleted-parent context; render content safely; provide
keyboard and assistive reading order.

**Ask first:** Approve display precision, ordering, evidence/source details,
recommendation section reference behavior, disclaimer, empty groups, list/
history navigation, and acceptance corpus.

**Never:** Recompute in browser or read live Profile/current JD as report truth;
upgrade Weak Evidence; invent a missing signal; hide source/rule identity; expose
foreign content; use color alone; make a recommendation mutate CV data.

## Story-specific rules and edge cases

- **MATCH-REVIEW-BR-001:** Presentation formatting may round a visible score
  only under the approved contract while retaining the canonical stored value
  and consistent accessible label.
- **MATCH-REVIEW-BR-002:** Empty groups are explicit meaningful states, not a
  reason to fabricate sample content or omit the report's category semantics.
- **MATCH-REVIEW-BR-003:** Missing/deleted current parents do not break an owned
  historical report; source summaries come from pinned identifiers/data under
  the approved privacy boundary.

| Scenario | Expected behavior |
| --- | --- |
| Owned complete report | Exact sources/rules and distinct stored groups/recommendations displayed |
| Skill list signal without support | Weak Evidence with available source context, never matched evidence |
| Requested signal absent | Missing/empty state without unsupported claim |
| Deleted logical JD | Historical report stays readable with clear deleted-parent banner and no new-work action |
| Foreign/missing report | Non-disclosing not-found state with no source leakage |
| Empty or long group content | Semantic headings/order, safe wrapping, usable keyboard/assistive navigation |
| Invalid API schema | Terminal safe contract error; no partial report UI |

</frozen-after-approval>

## Coverage by concern

| Concern | Authoritative coverage |
| --- | --- |
| Behavior | Stored explanation, Weak Evidence, missing, history, and accessibility scenarios |
| Contract | [contract.md](contract.md) and immutable Match Report representation |
| Backend | Owned read/list query, stored projection, source summary, no recomputation |
| Security | Report/source authorization, non-disclosure, sensitive content, safe rendering/logs |
| Validation | Response schema, canonical score/group/source identities, section references |
| Frontend | Loading/empty/error/deleted states, semantic groups, labels/order, keyboard behavior |
| Integration | Exact Story 2.4 fixtures and immutable query/cache identity |
| Verification | [verification.md](verification.md), quality counterexamples, a11y, and E2E |
