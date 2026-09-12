# Epic 3 Business Rules

## Ownership and source identity

- **E3-BR-001:** A Preview or Export may resolve only an authenticated User's
  owned immutable CV Version; authorization is server-derived.
- **E3-BR-002:** Every render identifies `cv_version_id`, `template_id`,
  `template_version`, and `renderer_version`.
- **E3-BR-003:** Preview and Export read only the saved CV Version snapshot and
  never merge current or unsaved CV Profile values.
- **E3-BR-004:** Foreign and missing CV Versions use the approved non-disclosing
  response; no source content or rendered fragment is exposed.

## Template catalog and selection

- **E3-BR-005:** Only active Templates compatible with the current product and
  User context appear in the selectable catalog.
- **E3-BR-006:** Template identity is stable; behavior-changing layout or mapping
  changes require an identifiable template version.
- **E3-BR-007:** An inactive, unavailable, unknown, or incompatible Template
  cannot create a Preview or Export.
- **E3-BR-008:** Selection alone creates no Preview, Export, or hidden trusted
  artifact unless `E3-DEC-003` explicitly approves persistence.

## Rendering and safety

- **E3-BR-009:** One approved section registry maps saved snapshot fields to
  Template slots; Preview and Export share it.
- **E3-BR-010:** Supported non-empty sections render; optional empty sections
  collapse without broken headings, whitespace, or page fragments.
- **E3-BR-011:** User content is rendered as inert text or through an approved
  safe formatting allowlist; markup-like content never executes.
- **E3-BR-012:** Deterministic ordering, date labels, link formatting, overflow,
  and page-break rules are frozen by renderer/template version.
- **E3-BR-013:** Renderer failure yields no partial trusted success and preserves
  the selected source so a safe retry is possible.

## Browser export and scope

- **E3-BR-014:** MVP Export revalidates the exact successfully reviewed source
  tuple and current Template availability, then invokes the browser print/HTML
  path; no AI provider or worker is required.
- **E3-BR-015:** Print cancel is not reported as an exported artifact unless the
  approved browser contract can prove completion.
- **E3-BR-016:** Repeated Preview or Export intent for unchanged source and
  renderer versions does not create duplicate unexplained persistent state.
- **E3-BR-017:** Server PDF, stored file artifacts, sharing, and background
  rendering are separately discovered work, not implicit Epic 3 scope.

## Team execution

- **E3-BR-018:** Multiple independent tasks may be `doing`; each names one owner,
  Story, branch/worktree, dependencies, scope, and verification evidence.
- **E3-BR-019:** Template registry, shared renderer, print stylesheet, or fixture
  changes require the matching coordination checkpoint before parallel work.
- **E3-BR-020:** New visual/product requirements are recorded as decisions or
  discovered work and do not silently expand a Story.
