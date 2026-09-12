# Epic 3 UX and Validation

## Shared journey

1. User chooses an owned saved CV Version.
2. User sees active compatible Templates with enough selection context.
3. User selects one Template and opens Preview.
4. Preview states the Version and Template being reviewed.
5. User inspects all supported content and invokes browser Export.
6. Returning from print preserves review context without claiming completion.

## E3-VAL-001 — Template selection

- Validate stable Template identity/version and current availability.
- Define loading, empty catalog, unavailable-after-selection, unsupported source,
  and retry states.
- Preserve the User's CV Version selection when a Template becomes unavailable.

## E3-VAL-002 — Snapshot projection

- Validate CV snapshot schema, supported section types, ordering, dates, URLs,
  text lengths, and nested collection limits before rendering.
- Empty optional sections are omitted; required structural failures stop the
  Preview rather than producing misleading partial output.
- Markup-like strings remain inert and legible.

## E3-VAL-003 — Layout and overflow

- Cover empty, minimal, normal, long, Unicode, RTL decision, long-word, long-URL,
  and multi-page data.
- Define deterministic truncation or wrapping; never silently drop required CV
  content to satisfy layout.
- Preview and print share content/order even when responsive presentation differs.

## E3-VAL-004 — Accessibility

- Use one document heading hierarchy, semantic section labels, meaningful link
  names, keyboard-operable controls, visible focus, and announced async errors.
- Template cards expose selected/unavailable state without color alone.
- Print-only and screen-only regions do not create duplicate assistive content.

## E3-VAL-005 — Export feedback

- Disable or guard Export until the exact Preview is ready.
- Explain unsupported/blocked print behavior and keep a safe retry path.
- Do not display “exported successfully” when browser completion is unknowable.
