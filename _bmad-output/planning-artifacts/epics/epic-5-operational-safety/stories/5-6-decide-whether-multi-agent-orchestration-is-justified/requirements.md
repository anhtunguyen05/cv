# Story 5.6 — Requirements

Shared [rules](../../business-rules.md), [contract](../../contracts.md),
[security](../../security-and-access.md), and [test strategy](../../test-strategy.md)
remain authoritative.

<frozen-after-approval reason="human-owned behavior — do not modify unless human renegotiates">

**Always:** Pin representative workload/baseline/environment; define metrics and
limitation threshold before measurement; include variance/failures; compare
simpler alternatives; assess safety/ownership/operability/migration/rollback;
record adopt/defer with owner/evidence/trigger; require human architecture review.

**Never:** Add agents because of preference/demo; change baseline after results;
hide cost/latency/failure/operational overhead; give agents broader tools or
writes; weaken validation/human approval; treat ADR adoption as implementation authority.

| Scenario | Expected behavior |
| --- | --- |
| Baseline meets thresholds | Explicit defer, no component/code/config, future trigger |
| Reproducible material limitation | Alternatives and bounded proposal reviewed against criteria |
| Results inconclusive/noisy | No adoption; expand evidence or defer |
| Proposed agent changes ownership/tool/write boundary | Reject or redesign before ADR adoption |
| Adopted decision | Migration/rollback/kill switch/owner and separate implementation authorization |
| Baseline/model/workload later changes | New measurement/ADR revision, prior evidence preserved |

</frozen-after-approval>

This is an architecture/evidence Story, not an implementation Story. Tasks may
create versioned measurement harness/config/docs artifacts after approval but
cannot add multi-agent runtime components.
