# Story 5.5 — Requirements

Shared [rules](../../business-rules.md), [contract](../../contracts.md), and
[test strategy](../../test-strategy.md) remain authoritative.

<frozen-after-approval reason="human-owned behavior — do not modify unless human renegotiates">

**Always:** Use synthetic versioned approved fixtures; pin source/expected/
engine/rule/schema/metric/tool versions; run matcher read-only; repeat unchanged
inputs; report fixture and aggregate metrics/counter-metrics/thresholds; emit
immutable safe evidence and fail regressions explicitly.

**Never:** Rewrite expected output during validation; use production User data;
update stored Match Reports; hide failing fixtures; equate repeatability with
quality; change threshold/rules/corpus silently; expose sensitive content.

| Scenario | Expected behavior |
| --- | --- |
| Same exact versions/input repeated | Identical canonical output/hash and repeatability pass |
| Classification/score/order differs | Fixture diagnostic and threshold/counter-metric result |
| Rule/schema/engine version changes | Separate comparable run or explicit incompatibility, never silent baseline rewrite |
| Empty/alias/negation/Weak/tie/Unicode/adversarial case | Approved expected classification/score/order evaluated |
| Mutation attempt or production data source | Validator fails closed before write/read of prohibited source |
| Threshold/fixture waiver | Named owner/reason/expiry evidence, not hidden pass |

</frozen-after-approval>

Evaluator owns read-only fixture loading, matcher invocation, canonicalization,
metrics and artifacts. Matcher remains Epic 2 owned. Verification proves no
database mutation, deterministic reruns, quality thresholds and CI failure.
