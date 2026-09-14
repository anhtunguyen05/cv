# Story 5.4 — Contract Slice

Refines `E5-CONTRACT-METRIC-001`.

| Boundary | Required metrics | Forbidden |
| --- | --- | --- |
| Matching | operation/result/failure duration and count, rule/version-approved labels | CV/JD text, User/resource IDs, skill strings |
| Provider/Patch | attempt and operation outcome, duration, retry/failure, approved cost/token buckets | prompt/output, model error text, Evidence, secrets |
| Export/job/system | invocation/status/failure/dependency/health under implemented contract | filenames/content, arbitrary routes/errors, instance secrets |

Alert contract records query/version, window/threshold, no-data, severity, owner,
route, dedupe/silence, runbook, recovery. Freeze vendor/topology, labels, SLOs,
retention/access and failure behavior in `E5-DEC-001`, `E5-DEC-005`.
