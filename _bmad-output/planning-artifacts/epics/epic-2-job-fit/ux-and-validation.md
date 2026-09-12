# Epic 2 UX and Validation

## Shared validation principles

- **E2-VAL-001:** Validate raw Job Description text by approved Unicode,
  newline, byte, and character rules. Preserve meaningful formatting while
  rejecting empty-after-normalization and over-limit values.
- **E2-VAL-002:** Company and role are optional bounded text fields. Omission,
  explicit null, empty input, trimming, and clearing semantics must be frozen
  before frontend/backend implementation.
- **E2-VAL-003:** Job Description updates require the approved current-revision
  or timestamp precondition; stale writes preserve User input and never create
  a revision.
- **E2-VAL-004:** Analysis output is schema-validated against a versioned closed
  vocabulary/normalization contract before persistence or display.
- **E2-VAL-005:** Match requests accept only source identifiers and an approved
  deduplication/precondition token. Score, evidence, and recommendations are
  server output and never writable fields.
- **E2-VAL-006:** Error `details` use stable field paths shared by fixtures and
  the web adapter. UI logic branches on code/path, never message text.

## Shared interaction states

Every surface covers loading, empty, validation, authorization/not-found,
retryable failure, terminal failure, and successful reload states.

| Surface | Required states |
| --- | --- |
| JD create/edit | pristine, dirty, validating, submitting, field/global error, stale conflict, ambiguous success reconciliation, saved |
| JD list/detail | loading, empty, active item, deleted historical context, not found, failure, refresh |
| Analysis | not requested, analyzing, successful, retryable failure, terminal failure, stale because a new revision is current |
| Comparison | source selection, missing CV Version, analysis required, submitting, conflict, success/navigation |
| Match Report | loading, grouped classifications, empty group, deleted-source banner, not found, failure |

## Source distinction

- Raw Job Description content is visibly labeled as User input.
- Analysis fields are visibly labeled as deterministic extraction and show the
  source revision plus rule version in accessible detail.
- Match Report output is labeled as deterministic comparison, not a hiring
  decision or guaranteed ATS result.
- A historical report shows the exact CV Version and Job Description revision;
  deleted parent state is clear without removing historical content.

## Accessibility

- Intake, edit, Analyze, Compare, and report navigation are keyboard usable.
- Labels and validation messages are programmatically associated; focus moves
  to a useful error summary or first invalid control after rejected submit.
- Score is never communicated by color alone. Matched, missing, and Weak
  Evidence groups have semantic headings and usable reading order.
- Dynamic analysis and matching outcomes are announced without stealing focus
  or causing repeated announcements.

## Cross-Story UI coordination

Stories 2.1–2.3 share Job Description query/mutation state and revision-aware
cache keys under `E2-COORD-JD-001`. Stories 2.3–2.5 share Analysis/Match schema
adapters under `E2-COORD-ANALYSIS-001` and `E2-COORD-MATCH-001`. One integration
owner serializes shared API client, route, and cache-invalidation changes.
