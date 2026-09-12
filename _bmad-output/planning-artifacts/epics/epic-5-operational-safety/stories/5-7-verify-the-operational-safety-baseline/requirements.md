# Story 5.7 — Requirements

Shared [rules](../../business-rules.md), [contract](../../contracts.md),
[security](../../security-and-access.md), and [test strategy](../../test-strategy.md)
remain authoritative.

<frozen-after-approval reason="human-owned behavior — do not modify unless human renegotiates">

**Always:** Define committed scope; require Story-owned completion evidence;
pin commit/config/contracts/environment/fixtures/commands; run approved cross-
control checks; validate evidence integrity/freshness/access; list every gap/
decision/waiver; obtain human verdict; keep failures; supersede rather than rewrite.

**Never:** Mark pass from planning/screenshots/stale/partial evidence; omit failed
or uncommitted controls; expose secrets/User content; silently waive; treat
post-MVP uncommitted Story as passed; let manifest mutate product state; use
baseline verdict to authorize production-destructive action.

| Scenario | Expected behavior |
| --- | --- |
| All committed controls pass with fresh evidence | Scoped pass plus explicit remaining decisions |
| Accepted bounded gap | Pass-with-gaps only with owner/reason/risk/expiry/trigger |
| Required evidence failed/missing/stale | Fail, no laundering, remediation/rerun path |
| Post-MVP Story not committed | Mark out of scoped verdict, not passed or silently missing |
| Artifact inaccessible/tampered/leaks canary | Fail evidence gate |
| New run after remediation | New manifest supersedes old; history preserved |

</frozen-after-approval>

Baseline owner coordinates evidence and cross-control tests but does not edit
Story-owned results. Human approvers own final verdict and waivers. Verification
itself checks manifest completeness, privacy, integrity, and reproducibility.
