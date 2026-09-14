# Epic 4 UX and Validation

## Shared journey

1. User opens an owned Match Report with unresolved areas.
2. User starts an Interview pinned to exact sources.
3. User answers each targeted question or explicitly cannot provide Evidence.
4. User requests generation and sees honest progress/failure/retry state.
5. User reviews source versus proposal, reason, and Evidence provenance.
6. User edits within bounds, rejects, or explicitly approves after revalidation.
7. Approval opens the new immutable Version; regeneration preserves history.

## E4-VAL-001 — Interview and questions

- Define eligible/unneeded/stale/active/completed/expired sessions and exact area
  and question ordering/version.
- Show why each question is asked and which Match area it addresses without
  implying missing Evidence exists.
- Preserve safe navigation/reload/back behavior and unsent answer drafts under
  the approved policy.

## E4-VAL-002 — Evidence answers

- Validate decoded raw answer length/encoding and a separate normalized view.
- Require exactly one answer or cannot-provide outcome; preserve original text
  and distinguish it from system/provider content.
- Cover empty, over-limit, markup-like, repeated, stale, lost-response,
  concurrent, correction, and unsupported-file/link cases.

## E4-VAL-003 — Patch validation

- Strictly validate schema/version, allowlisted target, exact old value type,
  new value limits, reason, Evidence citations, source tuple, and safe content.
- A provider citation to cannot-provide, nonexistent, foreign, or unrelated
  Evidence is terminal invalid output.
- User edits remain within the same allowed target and visibly retain provenance.

## E4-VAL-004 — Review and decisions

- Present old/new diff, source Version, reason, Evidence, provider/User-edit
  provenance, and current Patch status before actions.
- Confirm reject and approve; guard repeated/concurrent/stale actions; preserve
  context on safe retry.
- Never represent pending or invalid text as applied CV content.

## E4-VAL-005 — Accessibility

- Question progress, validation, generation status, diff semantics, Evidence
  references, confirmation, conflicts, and result links are keyboard accessible
  and announced without color alone.
- Long/Unicode content, empty Evidence, screen-reader reading order, focus after
  transition, and reduced-motion/loading behavior are included in fixtures.
