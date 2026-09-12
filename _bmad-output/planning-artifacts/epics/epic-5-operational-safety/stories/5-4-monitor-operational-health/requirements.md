# Story 5.4 — Requirements

Shared [rules](../../business-rules.md), [contract](../../contracts.md),
[security](../../security-and-access.md), and [test strategy](../../test-strategy.md)
remain authoritative.

<frozen-after-approval reason="human-owned behavior — do not modify unless human renegotiates">

**Always:** Instrument approved lifecycle boundaries; define metric version/unit/
status/failure; allowlist bounded labels; distinguish no-data/zero; aggregate and
retain by policy; authorize operator views; attach alert owner/runbook/recovery;
scan every sink with canaries.

**Never:** Label with User/email/resource/raw error/content/prompt; emit high-
cardinality free text; infer success before trusted commit; fabricate metrics for
unimplemented work; alert without owner/runbook; expose observability credentials.

| Scenario | Expected behavior |
| --- | --- |
| Supported success/failure | Exact counter/duration/status/category at approved boundary |
| Retry/duplicate/cancel/late result | Frozen attempt versus operation counting; no double success |
| No traffic or telemetry outage | Explicit no-data/delay signal, not healthy zero |
| Canary in source/error/label | Absent from metric/trace/dashboard/alert/export |
| SLO breach/recovery | Deduped routed alert, runbook, acknowledgment and resolution evidence |
| Cardinality growth/version change | Budget failure/review before production rollout |

</frozen-after-approval>

Producers own correct lifecycle emission; telemetry library owns schema/redaction;
operators own SLO/alerts/runbooks. Verification covers unit schema, integration,
cardinality/privacy, alert drill, access, and dashboard semantics.
