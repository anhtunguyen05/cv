# Story 5.7 — Contract Slice

Refines `E5-CONTRACT-BASELINE-001`.

| Manifest section | Required content |
| --- | --- |
| Scope | Included/excluded Stories/controls and classification rationale |
| Sources | Commit/config/contract/policy/fixture/tool/environment versions |
| Evidence | Story/check ID, owner, command/run, time, result, immutable safe artifact/integrity/freshness |
| Cross-control | Provider failure/no partial state, redaction canaries, deletion isolation, telemetry alert, match quality, ADR availability |
| Gaps | Unresolved production decisions, failures, known limitations, waiver owner/reason/risk/expiry/trigger |
| Verdict | `pass|pass_with_gaps|fail`, approvers/date, release/kill implications, remediation/rerun/supersedes |

The generator cannot convert missing/failed evidence into pass. Exact required
controls, freshness, integrity, waiver and approver policy are frozen in
`E5-DEC-009` before readiness.
