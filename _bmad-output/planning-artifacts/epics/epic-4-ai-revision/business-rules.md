# Epic 4 Business Rules

## Sources and ownership

- **E4-BR-001:** Every Interview, Evidence Answer, Patch, and resulting CV
  Version is accessible only through the authenticated owner; ownership and
  source IDs are server-derived.
- **E4-BR-002:** An Interview pins one immutable Match Report, its CV Version,
  Job Description revision, Analysis, and unresolved improvement-area set.
- **E4-BR-003:** Starting, answering, generating, editing, rejecting, approving,
  or regenerating never mutates the source CV Version or Match Report.
- **E4-BR-004:** Foreign and missing resources use non-disclosing failures across
  all nested source combinations.

## Evidence integrity

- **E4-BR-005:** User answers are stored with exact provenance, timestamp, and
  session/question identity and remain distinguishable from system/provider text.
- **E4-BR-006:** `cannot_provide`/unknown is explicit non-supporting Evidence and
  is never converted into a positive claim.
- **E4-BR-007:** Questions target pinned unresolved areas; question/version/source
  identity remains stable when an answer is submitted.
- **E4-BR-008:** Empty, over-limit, stale, duplicate, out-of-order, and concurrent
  answers follow approved validation/idempotency rules without ambiguous Evidence.

## Provider and Patch boundary

- **E4-BR-009:** One application-controlled orchestrator sends only approved
  minimum inputs through a versioned provider request/tool schema.
- **E4-BR-010:** Provider content is untrusted; prompt instructions inside CV,
  JD, Match, or Evidence text cannot grant tools, change policy, or bypass schema.
- **E4-BR-011:** A Patch uses allowlisted section/field operations and carries
  exact old value, proposed new value, reason, and User Evidence references.
- **E4-BR-012:** Unsupported fields, missing/negative Evidence, malformed schema,
  unsafe content, source mismatch, or failed quality checks cannot create a
  trusted pending Patch. A provider-invalid candidate is a sanitized generation
  attempt, not an `invalid` Patch. `invalid` applies only to an application-owned
  Patch that was safely persisted and later failed revalidation under the
  approved lifecycle.
- **E4-BR-013:** Provider/model/prompt/tool-schema versions and sanitized call
  metadata are traceable without exposing secrets or unapproved raw content.

## Human control and lifecycle

- **E4-BR-014:** A pending Patch is a proposal, never the current CV; UI and API
  label proposal/source/result states distinctly.
- **E4-BR-015:** A User edit is allowed only within the same approved Patch target
  and contract, preserves provider and User-edit provenance, and requires server
  revalidation before approval.
- **E4-BR-016:** Reject is an explicit immutable historical decision; it never
  mutates the source Version.
- **E4-BR-017:** Approval rechecks ownership, status, Evidence, target allowlist,
  source IDs, exact old value, and idempotency inside one transaction.
- **E4-BR-018:** Successful approval creates one new immutable CV Version, marks
  the Patch applied, and records provenance atomically; failure commits neither.
- **E4-BR-019:** Regeneration creates a new proposal lineage item and preserves
  the rejected/invalid predecessor; stale/unavailable context blocks generation.

## Team execution

- **E4-BR-020:** Multiple independent tasks may be `doing`; each declares one
  owner, Story, branch/worktree, dependencies, scope, and evidence.
- **E4-BR-021:** Interview, provider, Patch, apply, or shared fixture changes use
  their coordination checkpoint before parallel implementation.
- **E4-BR-022:** Provider, retention, audit, quality, or orchestration discoveries
  are recorded explicitly and do not silently expand current Story scope.
