# Story 4.3: Generate a Patch proposal — Requirements

Return to [Story overview](README.md); shared [rules](../../business-rules.md),
[security](../../security-and-access.md), [data/lifecycle](../../data-and-lifecycle.md),
and [test strategy](../../test-strategy.md) remain authoritative.

<frozen-after-approval reason="human-owned behavior — do not modify unless human renegotiates">

**Always:** Authorize and pin complete source/Evidence context; send only approved
minimum data through versioned schema; treat output as untrusted; validate target,
old/new values, Evidence, source, and safety; persist valid pending Patch in
Laravel; record sanitized outcome; preserve source Version.

**Never:** Give provider persistence/tools/secrets; obey instructions embedded in
User/source text; accept invented/negative/foreign Evidence; persist malformed or
unsupported output as pending; log raw sensitive payload by default; auto-apply.

| Scenario | Expected behavior |
| --- | --- |
| Eligible grounded provider DTO | One valid pending Patch with exact provenance |
| No submitted/supporting Evidence or stale context | Actionable rejection before provider or persistence |
| Malformed/unsupported/ungrounded/unsafe output | No pending Patch; sanitized terminal invalid outcome |
| Timeout/rate/unavailable/cancel/late response | Frozen retry/reconciliation; no duplicate trusted Patch |
| Duplicate/concurrent/lost generation request | One approved lineage/result under idempotency policy |
| Prompt injection or cross-User identifiers | Ignored/rejected, safely audited, no privilege/data leak |

</frozen-after-approval>

## Concern coverage

Behavior/backend cover eligibility, orchestrator, validation, persistence, and
transaction. Contract covers provider DTO, Patch and errors. Security covers
minimum disclosure, injection, secrets, logs, abuse. Validation covers schema,
allowlist, Evidence grounding, source/value/type/content. Frontend covers
request/progress/cancel/failure/retry/success without fake state. Verification
uses provider fake, adversarial corpus, evaluation, MySQL, and browser evidence.
