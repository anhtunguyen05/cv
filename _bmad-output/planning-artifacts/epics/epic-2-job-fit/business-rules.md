# Epic 2 Shared Business Rules

These rules apply across Stories 2.1–2.5. Story-specific files reference these
IDs instead of redefining them.

## Ownership and source integrity

- **E2-BR-001:** Every Job Description, revision, Analysis, and Match Report is
  readable only through the authenticated owning User. Ownership is derived on
  the server and is never writable client input.
- **E2-BR-002:** A Job Description has one stable logical ULID and one or more
  immutable revisions. Creation atomically creates revision 1 and marks it
  current.
- **E2-BR-003:** Raw Job Description text is preserved exactly after transport
  decoding. A separately derived normalized view may support validation and
  Analysis, but can never replace the stored raw source.
- **E2-BR-004:** A valid update creates exactly one new immutable revision and
  atomically makes it current. Earlier revisions and their source identity do
  not change.
- **E2-BR-005:** Only the current revision of a non-deleted Job Description may
  be edited, analyzed, or selected for a new Match Report.
- **E2-BR-006:** Logical deletion removes a Job Description from active work but
  preserves owned historical revisions, successful Analyses, and existing
  Match Reports required for reproducibility.

## Analysis and matching

- **E2-BR-007:** One successful Analysis interprets exactly one immutable Job
  Description revision under one explicit `analysis_rule_version`.
- **E2-BR-008:** Missing or unrecognized signals are represented as absent or
  unknown. Analysis never fabricates role, skills, responsibility, seniority,
  soft-skill, keyword, or domain/context values.
- **E2-BR-009:** Repeating Analysis for the same revision and rule version
  produces the same normalized result. Retry behavior must not create competing
  successful results for the same deterministic key.
- **E2-BR-010:** A new revision starts without a successful Analysis. A prior
  revision's Analysis is never silently reused for current work.
- **E2-BR-011:** A new Match Report consumes exactly one owned immutable CV
  Version and the successful Analysis of the selected Job Description's current
  non-deleted revision.
- **E2-BR-012:** A Match Report pins `cv_version_id`, `job_description_id`,
  `job_description_revision_id`, `analysis_id`, `analysis_rule_version`,
  `matching_rule_version`, and its result schema version.
- **E2-BR-013:** Match Report output is immutable. Opening a report reads the
  stored result and pinned sources; it never recomputes against live Profile or
  current Job Description state.
- **E2-BR-014:** The same pinned sources and matching-rule version produce the
  same score, classifications, and recommendations.

## Evidence and failure behavior

- **E2-BR-015:** A skill present only in a CV skills list without approved
  project or experience support is Weak Evidence, not fully evidenced.
- **E2-BR-016:** A signal absent from both selected sources cannot become a
  claim or recommendation. When available, a recommendation identifies the
  related CV section without mutating it.
- **E2-BR-017:** Analysis or matching failure leaves raw sources and previous
  successful derived records unchanged. Partial output is never presented or
  persisted as successful.
- **E2-BR-018:** MVP Analysis and matching are synchronous and provider-free
  unless an approved measured decision establishes an explicit async contract.

## Team execution

- **E2-BR-019:** Multiple Stories and tasks may be active concurrently when
  dependencies are satisfied and shared revision, analysis, matching, and test
  boundaries have one recorded coordination owner.
- **E2-BR-020:** No task may change an Epic 1 CV Version snapshot contract,
  introduce an LLM/worker dependency, or broaden into ATS optimization without
  separate approved scope.
