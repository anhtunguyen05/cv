# Deterministic Match Quality Baseline Policy

This policy governs the synthetic quality artifact produced by
`php artisan match:quality --json`. It is a reviewable local/CI control, not a
production readiness verdict.

## Artifact contract

Each artifact is one JSON object with `artifact_version`, `verdict`, `exit_code`,
the pinned `source_sha`, `corpus_sha256`, engine/rule/schema/metric/tool versions,
per-case hashes, aggregate metrics, and safe diagnostics. It must contain only
synthetic fixture identifiers and bounded diagnostic codes; no User, CV, JD,
prompt, provider response, credential, or mutable product state is allowed.

The accepted clean result is `PASS` with exact classifications and ordering,
score delta no greater than `0.01`, zero unsupported-claim counterexamples,
identical repeatability hashes, and runtime at or below 60 seconds. Any
`QUALITY_REGRESSION`, `REPEATABILITY_FAILURE`, invalid-input, or infrastructure
exit is a failed gate and must remain available for triage.

## CI handling

The API workflow writes one artifact per run and uploads it with a seven-day
retention period. The artifact is restricted to the workflow's repository
access permissions and is disposable evidence; it is never an alternate product
record or an accepted baseline. The retention/access choice remains subject to
the Epic 5 environment decision `E5-DEC-008`.

## Baseline change and triage

1. Preserve the failing artifact and record its source, corpus, engine, rule,
   schema, metric, and tool versions.
2. Classify the failure as implementation regression, fixture defect, intended
   rule change, environment/infrastructure failure, or unsupported claim.
3. Do not edit expected output or thresholds in place. A baseline change must
   use a new manifest/corpus version, include a reason and rollback reference,
   and receive review from someone other than its author.
4. A temporary waiver must name an owner, reason, evidence, risk, rollback, and
   expiry no later than seven days. Expired or missing waivers fail closed.
5. Rerun the clean and negative suites from the new pinned source before any
   new baseline is considered.

No waiver changes the matcher, writes product data, authorizes provider traffic,
or enables destructive production operations.
