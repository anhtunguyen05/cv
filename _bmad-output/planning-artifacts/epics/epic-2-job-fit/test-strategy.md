# Epic 2 Test Strategy

## Required evidence layers

| Layer | Epic 2 responsibility |
| --- | --- |
| Unit/domain | Revision rules, signal normalization, rule-version selection, evidence classification, score calculation, ordering, and failure classification |
| Laravel feature | Request/response/status/error matrices, session, policy, stale/deleted state, deduplication, and safe serialization |
| MySQL integration | Atomic create/revision/report writes, constraints, deterministic keys, rollback, collation, ordering, and concurrency |
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

## E2-TEST-002 — Analysis corpus

A versioned corpus records raw input, expected normalized signals, explicit
absent/unknown results, aliases, repeated terms, negation/qualification cases,
and `analysis_rule_version`. Running the same fixture repeatedly must produce
the same canonical result.

## E2-TEST-003 — Match evaluation fixtures

Each fixture pins one immutable CV Version snapshot, one Analysis, expected
matched/missing/Weak Evidence classifications, recommendations, score, and
`matching_rule_version`. Tests include unsupported-claim counterexamples and
rounding/tie-order cases.

The quality threshold and fixture approval owner remain open under
`E2-DEC-005`; repeatability alone does not prove useful or truthful matching.

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
5. MySQL and Playwright end-to-end acceptance checkpoint.

Stories may implement independent work in parallel after the relevant gate;
shared fixture or harness changes are serialized through coordination records.
