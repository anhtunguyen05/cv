# Data Standard

References: AD-1, AD-3, AD-4, AD-5, AD-11, AD-15, AD-16.

- **DATA-STD-001:** MySQL 8.4 is the canonical production-compatible datastore. SQLite does not
  prove migration, query, collation, or constraint compatibility.
- **DATA-STD-002:** New product aggregates use ULID string primary/public IDs. Existing Laravel
  users and system tables keep their bigint keys.
- **DATA-STD-003:** Store timestamps in UTC and expose ISO-8601 UTC strings at API boundaries.
- **DATA-STD-004:** Structured CV content is canonical. Raw text is input/audit material, never
  the sole CV domain representation.
- **DATA-STD-005:** Preserve raw input, derived analysis, and AI proposal as separate data types.
- **DATA-STD-006:** CV Versions and Job Description revisions are immutable. Derived records pin
  their exact source IDs and applicable rule/template versions.
- **DATA-STD-007:** Logical deletion may hide a resource from new work but must not invalidate a
  historical report that is required to remain reproducible.
- **DATA-STD-008:** Use JSON only for intentionally variant, validated structures. Index and
  constrain ownership, lifecycle, source, and lookup fields explicitly.
