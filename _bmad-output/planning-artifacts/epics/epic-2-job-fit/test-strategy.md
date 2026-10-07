# Epic 2 Test Strategy

## Required evidence layers

| Layer | Epic 2 responsibility |
| --- | --- |
| Unit/domain | Revision rules, signal normalization, rule-version selection, evidence classification, score calculation, ordering, and failure classification |
| Laravel feature | Request/response/status/error matrices, session, policy, stale/deleted state, deduplication, and safe serialization |
| PostgreSQL integration | Atomic create/revision/report writes, constraints, deterministic keys, rollback, collation, ordering, and concurrency |
| Vue unit/component | Schema/error adapters plus every intake, analysis, comparison, and report interaction state |
| Contract | Versioned fixtures consumed by backend and frontend without local variants |
| Playwright | Save/reload JD, revise/analyze current revision, create report from CV Version, review evidence, and historical deleted-source report |

Every canonical AC maps to tasks in its Story `verification.md`. Planned
commands are not completion evidence; tasks replace plans with command/result
or review evidence before `done`.

## E2-TEST-001 — Versioned Job Description fixtures

Fixtures cover Unicode, multiline formatting, empty/oversized input, optional
metadata, multiple revisions, logical deletion, stale updates, lost responses,
ownership boundaries, and deterministic ordering.

`TASK-2-1-01` promotes the immutable corpus to
`docs/contracts/jd/fixtures/job-description-v1.json`; `TASK-2-3-01` promotes
analysis examples to `docs/contracts/jd/fixtures/analysis-v1.json`; and
`TASK-2-4-01` promotes matching examples to
`docs/contracts/jd/fixtures/match-report-v1.json`. PHP and TypeScript consume
these files directly; neither keeps a local variant.

## E2-TEST-002 — Analysis corpus

A versioned corpus records raw input, expected normalized signals, explicit
absent/unknown results, aliases, repeated terms, negation/qualification cases,
and `analysis_rule_version`. Running the same fixture repeatedly must produce
the same canonical result.

The approved v1 corpus has at least 40 reviewed examples (at least 20 English
and 20 Vietnamese/mixed-language) plus 12 held-out counterexamples. It covers
required/preferred cues, aliases, negation, malformed source text, absent and
unknown states, and Unicode normalization. Canonical JSON output must be byte
identical across repeated runs for one pinned rule version.

## E2-TEST-003 — Match evaluation fixtures

Each fixture pins one immutable CV Version snapshot, one Analysis, expected
matched/missing/Weak Evidence classifications, recommendations, score, and
`matching_rule_version`. Tests include unsupported-claim counterexamples and
rounding/tie-order cases.

The approved gate has at least 40 reviewed evaluation fixtures and 12 held-out
counterexamples. It requires no unsupported claim in any fixture, exact
classification agreement for every reviewed signal, and score agreement within
one point. Held-out cases are not used to tune vocabulary or weights. Pc
approves the corpus; a named integration owner records command evidence.
Repeatability alone does not prove useful or truthful matching.

## E2-TEST-004 — Historical reproducibility

Create a report, revise then logically delete its Job Description, edit the
source Profile, and prove the report's pinned CV Version, revision, Analysis,
classifications, and score remain unchanged and owner-readable.

## E2-TEST-005 — Isolation and safety

Tests use two Users and mixed ownership combinations for every nested source.
They assert non-disclosing failures, no partial writes, sanitized logs, safe
text rendering, rate-limit atomicity, and disposable-data enforcement.

## Integration gates

1. Job Description revision fixtures and persistence checkpoint.
2. Analysis schema/rule-version fixture checkpoint.
3. Match scoring/evidence fixture and quality threshold checkpoint.
4. Frontend adapter compatibility checkpoint.
5. PostgreSQL and Playwright end-to-end acceptance checkpoint.

Stories may implement independent work in parallel after the relevant gate;
shared fixture or harness changes are serialized through coordination records.
