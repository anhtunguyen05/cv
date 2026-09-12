# Epic 4 Test Strategy

## Required evidence layers

| Layer | Epic 4 responsibility |
| --- | --- |
| Unit/domain | Eligibility, lifecycle, Evidence provenance, Patch allowlist/validation, stale check, application transform |
| Laravel feature | API/envelopes, ownership graph, idempotency, conflicts, provider failure mapping, decisions |
| MySQL integration | Source pinning, constraints, lineage, concurrent decisions, apply rollback and one-Version guarantee |
| Provider contract | Request minimization, schema/tool enforcement, versions, timeout/retry/cancel, malformed/adversarial output |
| Vue unit/component | Interview states, answer validation, proposal diff/edit, confirmations, conflicts, accessibility |
| Security/evaluation | Prompt injection, unsupported claims, secret/log leakage, Evidence grounding, quality/cost/latency gates |
| Playwright | Start, answer/decline, generate, review/edit/reject/approve/regenerate, stale/failure/foreign paths |

## E4-TEST-001 — Source and Evidence corpus

Synthetic fixtures pin Match Report, CV Version, JD revision, unresolved areas,
question-set version, User answers, cannot-provide outcomes, and source markers.

## E4-TEST-002 — Provider adversarial corpus

Cover malformed JSON, unsupported target, missing/wrong/negative Evidence,
invented claim, prompt injection in every source, unsafe markup/URL, oversized
text, cross-User ID, wrong old value, partial/duplicate operations, timeout,
rate limit, and ambiguous/lost result.

## E4-TEST-003 — Patch quality evaluation

Human-approved cases define groundedness, target correctness, factual
preservation, useful improvement, false-positive counter-metrics, refusal, and
invalid-output thresholds across exact prompt/schema/model versions.

Repeatability is not assumed for provider prose; deterministic validation,
source pinning, Evidence support, and acceptance thresholds are required.

## E4-TEST-004 — State and transaction races

Cover duplicate start/answer/generate/edit/reject/approve/regenerate, concurrent
approve versus reject/apply, stale old value/context, provider success after
cancel/timeout, transaction failure at each write, and lost success response.

## E4-TEST-005 — Privacy and isolation

Use two Users and mixed source IDs. Assert minimum provider payload, no foreign
data, no secrets/raw content in logs/errors/audits, sanitized correlation, and
retention/deletion behavior under approved Epic 5 policy.

## Integration gates

1. Match/CV source tuple and Interview eligibility checkpoint.
2. Evidence question/provenance/lifecycle fixture checkpoint.
3. Provider request/result and Patch schema/validation checkpoint.
4. Review/edit/reject/regenerate state checkpoint.
5. Atomic apply/new Version provenance checkpoint.
6. Security, evaluation, MySQL, and Playwright acceptance checkpoint.
