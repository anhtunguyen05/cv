# Phase 5 — Observability, Safety, and Advanced AI

Status: `planned`  
**Depends on:** Phase 4

## Outcome

The system is diagnosable and safer to operate in production while preserving
the same validation, evidence, and human-approval boundaries.

## Scope

Add sanitized `ai_tool_calls`, prompt/provider metadata, timeout/retry/rate
limit handling, redacted structured logs, retention/deletion behavior, queue
metrics, export/analysis monitoring, and deterministic review/ATS validators.
Only introduce multi-agent decomposition after a measured single-orchestrator
limitation.

## Security design

Never log provider keys or raw CV/JD content by default. Validate every provider
response before persistence. Store only the metadata needed for debugging and
audit. Add data deletion behavior for messages, tool calls, exports, and source
documents before production release.

## Operational verification

Test provider timeout, quota/rate-limit, malformed JSON, retry, failed jobs,
redaction, retention, and deletion. Add dashboards or documented queries for
latency, failures, patch rejection rate, and export status. Keep a fake provider
for deterministic CI.

```powershell
cd apps/api; php artisan test
cd ../web; npm run type-check; npm run build; npm run lint
cd ../worker; python -m unittest discover -s tests
```

## Checkpoint / exit criteria

- Operators can diagnose failures without sensitive payloads.
- Provider failures are bounded and user-visible.
- AI claims retain evidence provenance and approval history.
- Users can delete stored data according to the documented policy.
- Any multi-agent work has an explicit decision record and preserves tool
  boundaries.

See [`github-issues.md`](github-issues.md).
