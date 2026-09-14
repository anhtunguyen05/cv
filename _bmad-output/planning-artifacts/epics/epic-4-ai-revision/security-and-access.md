# Epic 4 Security and Access

## E4-SEC-001 — Ownership graph

- Authorize every Interview/Answer/Patch action through the complete owned
  Match Report and source CV Version graph.
- Re-resolve ownership and source consistency server-side for generate, edit,
  reject, approve, and regenerate; client route state is not authorization.
- Missing and foreign nested combinations remain non-disclosing.

## E4-SEC-002 — Minimum provider disclosure

- Send only approved fields/fragments needed for the specific Patch target.
- Never send credentials, cookies, tokens, unrelated CV/JD sections, internal
  logs, database identifiers not required by the provider schema, or other Users' data.
- Provider region, subprocessors, training/retention, deletion, and consent/legal
  basis require explicit approval before production use.

## E4-SEC-003 — Prompt injection and untrusted output

- Treat CV, JD, Match, Evidence, and provider text as untrusted data, never
  higher-priority instructions.
- The orchestrator exposes only fixed allowlisted tools/DTOs and no persistence
  capability; output must pass strict schema, type, target, Evidence, content,
  and source validation.
- Escape proposal/reason content in UI and reject unsafe URLs/markup/control data.

## E4-SEC-004 — Secrets, logs, and audit

- Provider credentials remain server-side secret configuration and are redacted
  from API, logs, errors, traces, fixtures, and browser bundles.
- Ordinary logs omit raw CV/JD/Evidence/prompt/output. Sanitized audits reference
  actor/resource IDs, contract/model/prompt versions, outcome, latency class,
  token/cost class, and correlation ID under Epic 5 retention policy.

## E4-SEC-005 — Abuse and reliability

- Apply rate, concurrency, cost, payload, token, timeout, retry, and circuit
  limits per User/session/provider operation.
- Idempotency and leases prevent duplicate generation/apply while allowing safe
  reconciliation after lost responses.
- Failure/cancellation cannot leave a provider result or partial Version trusted.

## E4-SEC-006 — Human control

- UI clearly labels User Evidence, provider proposal, User-edited proposal,
  current source, and applied result.
- Approval requires a fresh explicit User action; no prechecked consent,
  background apply, or provider-triggered decision is allowed.
