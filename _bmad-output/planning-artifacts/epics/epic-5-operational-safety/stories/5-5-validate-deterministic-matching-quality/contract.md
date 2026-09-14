# Story 5.5 — Contract Slice

Refines `E5-CONTRACT-EVALUATION-001`.

| Input | Output | Guard |
| --- | --- | --- |
| Corpus/version; synthetic CV Version, JD Analysis, expected Match Report; engine/rule/schema/metric/tool versions | Per-fixture canonical hash, repeatability runs, classification/score/order metrics, counter-metrics, diagnostics, threshold and overall verdict | Read-only matcher boundary, no persistence repository/write capability, prohibited-data detector |

CLI/CI exit codes distinguish pass, quality regression, repeatability failure,
invalid fixture/version, and infrastructure error. Freeze corpus governance,
metrics/thresholds, diagnostics, run count, performance, artifact/waiver/change
policy in `E5-DEC-006`, `E5-DEC-008`.
