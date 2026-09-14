# Epic 5 Data and Lifecycle

## Operational data classes

| Class | Authority | Default character |
| --- | --- | --- |
| Trusted product records | Laravel domain/persistence | Never derived from audit/metrics; follow product lifecycle |
| Provider/job attempts | Application operational store | Sanitized, append-only outcome metadata |
| Metrics/traces/logs | Observability system | Content-free, bounded labels, aggregation/expiry policy |
| Evaluation fixtures/results | Version control/CI artifact store | Synthetic, versioned, immutable evidence |
| Retention/deletion request | Controlled operational workflow | Scoped state machine plus non-sensitive audit |
| Architecture/readiness evidence | Versioned docs/artifacts | Reviewed, immutable per decision/run |

## E5-DATA-001 — Audit/event integrity

- Append-only identity, timestamps, outcome, versions, correlation, redaction
  version, and approved references; no product-state mutation capability.
- Failed audit write follows approved fail-open/fail-closed policy per operation,
  with local sensitive data never used as fallback logging.
- Operator queries are bounded, paginated, filtered, access-audited, and safe exported.

## E5-DATA-002 — Retention/deletion dependency graph

Inventory direct User data, immutable/historical source chains, derived trusted
records, provider/job metadata, audits, metrics, caches, object storage, backups,
test artifacts, and external processors. Policy defines delete/anonymize/retain/
aggregate order, integrity constraints, legal hold, backup expiry, external
propagation, failure checkpoint, rerun, and proof.

## E5-DATA-003 — Operational state machines

```text
Deletion request: requested -> scoped -> approved -> running -> completed | partial_retryable | terminal_failed
Provider attempt: requested -> running -> succeeded | retryable_failed | terminal_failed | cancelled
Alert: normal -> firing -> acknowledged -> mitigated -> resolved -> reviewed
Baseline: planned -> running -> passed | passed_with_gaps | failed -> superseded
```

Every transition has owner/time/reason/precondition/idempotency and allowed next
actions. Partial deletion or job state never masquerades as success.

## E5-DATA-004 — Evidence reproducibility

Quality, ADR, and baseline artifacts pin exact source versions and environment.
Reruns create new artifacts; they do not rewrite prior evidence. Artifact
retention and access follow their approved data classification.
