---
title: CareerFitCV
status: final
created: 2026-09-01
updated: 2026-09-01
---

# PRD: CareerFitCV

## 0. Document Purpose

This is the canonical product requirements document for CareerFitCV. It
normalizes the product vision in `README.md` and the implementation sequence in
`docs/implementation-plan.md` into capability-focused requirements for product,
UX, architecture, and implementation work. The repository is currently a
scaffold; this PRD describes the MVP behavior to build and explicitly separates
later AI capabilities from the first release. Technical implementation details
and source reconciliation are recorded in `addendum.md`.

## 1. Vision

CareerFitCV helps students, freshers, and job seekers tailor a structured CV to
one target Job Description. It makes gaps visible, turns a user's actual
experience into evidence, and produces a clear CV version that is ready to
review and export.

The MVP focuses on a trustworthy foundation: a user controls their CV data, a
Job Description is compared against an exact CV Version, the Match Report is
explainable, and the Preview/Export result comes from saved data rather than
unsaved text. AI interview and patch generation remain later capabilities so
the first release can validate the core matching workflow without depending on
an external model.

## 2. Target User

### 2.1 Jobs To Be Done

- When applying for a specific role, understand whether my CV demonstrates the
  required skills.
- When my CV is generic, identify which sections need stronger evidence.
- When tailoring a CV, keep different role-specific versions without losing my
  original content.
- When I am ready to apply, see and export the exact CV Version I reviewed.

### 2.2 Non-Users (v1)

- Recruiters managing candidate pipelines.
- Employers evaluating or ranking applicants.
- Users seeking automatic job applications or LinkedIn integration.
- Users expecting automatic PDF/DOCX parsing in the MVP.

### 2.3 Key User Journeys

- **UJ-1. Minh creates a reusable CV foundation.**
  - **Persona + context:** Minh is a final-year student creating a structured
    CV for the first time.
  - **Entry state:** Minh is authenticated and has no saved CV Profile.
  - **Path:** Minh creates a profile, enters personal information, summary,
    skills, education, projects, and experience, then saves a draft and names a
    CV Version.
  - **Climax:** Minh reloads the page and sees the same structured content.
  - **Resolution:** Minh has a reusable draft and an immutable named CV Version
    ready to compare with a Job Description.

- **UJ-2. Lan checks a CV against a target role.**
  - **Persona + context:** Lan is applying for a frontend internship and wants
    specific feedback before submitting her application.
  - **Entry state:** Lan is authenticated, has a CV Version, and has copied a
    target Job Description.
  - **Path:** Lan creates a Job Description, reviews the extracted role and
    skills, selects a CV Version, and requests a comparison.
  - **Climax:** The Match Report shows the score, matched skills, missing
    skills, weak evidence, and recommendations tied to the CV.
  - **Resolution:** Lan knows which CV sections to improve and can return to
    her draft without changing the saved source Version.

- **UJ-3. Minh previews and exports the reviewed CV.**
  - **Persona + context:** Minh has selected the CV Version that best fits a
    role and wants a submission-ready artifact.
  - **Entry state:** Minh is authenticated and viewing a saved CV Version.
  - **Path:** Minh selects a Template, inspects the Preview, starts browser
    print/export, and confirms the rendered result.
  - **Climax:** The exported artifact reflects the selected CV Version and
    Template, not unsaved editor state.
  - **Resolution:** Minh has a copy ready to submit and the source Version
    remains available for future tailoring.

## 3. Glossary

- **User** — An authenticated student, fresher, or job seeker using CareerFitCV.
- **CV Profile** — A user's editable source collection of structured CV data.
- **CV Version** — A named snapshot of a CV Profile used for comparison,
  preview, or export. A saved CV Version is immutable.
- **Job Description** — A user's saved target-role input, including raw text and
  extracted role requirements.
- **Match Report** — A comparison result for exactly one CV Version and one Job
  Description.
- **Evidence** — CV content that supports a claimed skill or capability,
  especially in a project or experience item.
- **Weak Evidence** — A skill signal present without sufficient supporting
  Evidence in a project or experience item.
- **Template** — A selectable presentation format that renders a CV Version.
- **Preview** — The rendered, reviewable representation of a CV Version using a
  Template.
- **Export** — A user-initiated copy of a Preview suitable for submission.
- **Patch** — A proposed structured change to a CV Version. Patch is a later
  capability and is not part of the MVP apply workflow.

## 4. Features

### 4.1 Account and access

**Description:** A User can establish and access an account so their CV Profile,
Job Description, Match Report, and CV Version data remain private. The MVP
requires a web experience with authenticated access.

**Functional Requirements:**

#### FR-1: Account access

The User can register, sign in, sign out, and retrieve their current account.
Realizes UJ-1, UJ-2, and UJ-3.

**Consequences (testable):**

- A valid User can complete sign-in and access protected product capabilities.
- An unauthenticated request cannot read or modify User-owned data.
- Signing out removes access to protected capabilities until the User signs in
  again.

#### FR-2: Ownership isolation

The system restricts every User-owned resource to its owning User.

**Consequences (testable):**

- A User cannot retrieve, update, delete, compare, preview, or Export another
  User's CV Profile, CV Version, Job Description, or Match Report.
- Unauthorized resource access does not reveal whether another User's resource
  exists.

### 4.2 Structured CV Profile and CV Version

**Description:** A User maintains one or more CV Profiles as structured data.
The editable CV Profile is separate from saved CV Versions. A User can preserve
an original Version while creating a role-specific Version. Realizes UJ-1 and
supports UJ-2/UJ-3.

**Functional Requirements:**

#### FR-3: Create and edit CV Profile

The User can create and edit structured personal information, summary, skills,
education, experience, projects, certificates, languages, and activities.

**Consequences (testable):**

- The User can save and reload each supported section without converting it to
  an unstructured text blob.
- The system validates required fields, field types, and content limits before
  saving.
- Optional empty sections do not prevent saving or Preview.

#### FR-4: Create immutable CV Version

The User can create a named CV Version from the current CV Profile and select a
CV Version for downstream comparison or rendering.

**Consequences (testable):**

- A saved CV Version contains a complete structured snapshot and a stable name.
- Editing the CV Profile does not change an existing CV Version.
- The User can list and open their CV Versions in reverse creation order.

#### FR-5: Preserve source integrity

The system keeps the CV Profile and CV Version data distinct so later
comparison, Preview, and Export operations identify their exact source.

**Consequences (testable):**

- A Match Report references one exact CV Version.
- A Preview and Export reference one exact CV Version.
- The system does not overwrite a saved CV Version as a side effect of editing
  the CV Profile.

### 4.3 Job Description intake and analysis

**Description:** A User saves a target Job Description and receives a
structured interpretation of its role, skills, responsibilities, and other
signals. The MVP accepts pasted Job Description text. Realizes UJ-2.

**Functional Requirements:**

#### FR-6: Save Job Description

The User can create, view, update, and delete a Job Description containing raw
text and optional company and role information.

**Consequences (testable):**

- Empty or over-limit raw text is rejected with an actionable validation error.
- A saved Job Description can be reopened with its original raw text intact.
- Only the owning User can access or modify the Job Description.

**MVP lifecycle policy:**

- A Job Description has a stable logical identity and one or more immutable
  revisions. Creating a Job Description creates its first revision.
- Editing creates a new revision and makes it current; it never mutates a
  revision already used by analysis or a Match Report.
- Derived analysis belongs to a revision. Existing analysis is preserved; the
  new revision starts without a successful analysis and must be analyzed
  explicitly before it is used for a new Match Report.
- New Match Reports may be created only against the current revision of a
  non-deleted Job Description, and only after that revision has been analyzed
  successfully.
- Historical revisions remain immutable and readable through their preserved
  analyses and existing Match Reports; they are not used for new Match Reports.
- Deleting is a logical deletion of the stable resource for new operations. It
  is removed from normal active lists and cannot be edited, analyzed, or used
  for a new comparison, but its revisions, analyses, and historical Match
  Reports remain accessible to the owning User and reproducible from their
  pinned source state.

#### FR-7: Analyze Job Description

The system extracts role, required skills, nice-to-have skills,
responsibilities, keywords, seniority, soft skills, and domain/context when
those signals are present.

**Consequences (testable):**

- The analysis is stored separately from raw Job Description text.
- The User can distinguish extracted signals from the original input.
- Missing or unrecognized signals are represented as absent/unknown rather than
  fabricated.
- The analysis identifies the exact Job Description revision and analysis-rule
  version from which it was derived.

### 4.4 Deterministic CV-to-Job Description matching

**Description:** The system compares a selected CV Version with a selected Job
Description and produces an explainable Match Report. MVP matching is
deterministic and does not require an LLM. Realizes UJ-2.

**Functional Requirements:**

#### FR-8: Generate Match Report

The User can generate a Match Report for one CV Version and one Job Description.

**Consequences (testable):**

- The report contains an overall score, matched skills, missing skills, Weak
  Evidence areas, and recommendations.
- The report identifies the exact CV Version, Job Description, and Job
  Description revision used.
- The persisted report source includes stable Job Description and revision
  identifiers, together with the analysis-rule and matching-rule versions.
- Repeating the comparison with unchanged inputs and rules produces the same
  result.

#### FR-9: Explain skill evidence

The system classifies skill signals using CV Profile sections and Evidence,
distinguishing matched skills from Weak Evidence and missing skills.

**Consequences (testable):**

- A skill listed only in a skills list is not treated as fully evidenced when
  no supporting project or experience Evidence exists.
- A recommendation links to the relevant CV section when a source section is
  available.
- A Match Report does not claim a skill that is absent from both the Job
  Description and the selected CV Version.

### 4.5 Template Preview and Export

**Description:** A User selects a Template to render a saved CV Version, reviews
the Preview, and initiates an Export. MVP Export uses the browser's print/HTML
path; server-side or worker-generated PDF is later. Realizes UJ-3.

**Functional Requirements:**

#### FR-10: Render CV Version Preview

The User can select an active Template and view a Preview of a CV Version.

**Consequences (testable):**

- Preview renders all supported CV sections and safely handles empty sections.
- Preview reflects the selected CV Version and Template only.
- User-provided content is escaped/rendered safely and does not execute as
  markup.

#### FR-11: Export reviewed CV

The User can initiate an Export from a Preview using the selected CV Version
and Template.

**Consequences (testable):**

- Export output corresponds to the selected saved CV Version, not unsaved draft
  editor state.
- The User can retry after an Export failure and receives an actionable error.
- Export does not require an AI provider.

### 4.6 Later AI-assisted revision

**Description:** After the MVP, CareerFitCV may ask targeted questions for
missing Evidence and propose a Patch for User review. This capability extends
the MVP workflow but does not change the MVP's deterministic Match Report or
source-integrity rules.

**Functional Requirements:**

#### FR-12: Propose evidence-based Patch *(post-MVP)*

The system may generate a Patch from existing CV data and User-provided
Evidence for explicit review before any new CV Version is created.

**Consequences (testable when implemented):**

- A Patch identifies old value, new value, reason, and Evidence sources.
- Unsupported fields, missing Evidence, and stale source values are rejected.
- A Patch cannot change a CV Version without explicit User approval.

**Out of Scope:**

- Automatic application of a Patch.
- Claims not supported by existing content or User-provided Evidence.
- Multi-agent orchestration in the MVP.

## 5. Cross-Cutting Non-Functional Requirements

### NFR-1: Privacy and authorization

User-owned CV and Job Description content must be protected by server-side
authorization. Sensitive content must not be included in logs unless required
for an explicitly documented diagnostic purpose.

### NFR-2: Data integrity and traceability

Every Match Report, Preview, and Export must identify the source CV Version.
Saved CV Versions must remain immutable. Derived analysis must remain
distinguishable from raw User input. Job Description revisions used by analysis
or Match Reports must remain identifiable and reproducible after later edits or
logical deletion of the parent Job Description.

### NFR-3: Usability and accessibility

The web experience must expose loading, empty, validation, authorization, and
failure states. Core create/edit/compare/preview actions must be keyboard
usable and present labels/errors associated with their controls.

### NFR-4: Reliability

A failed analysis or Export must not silently create partial product state. A
User must receive a retryable outcome or a clear terminal error.

### NFR-5: Determinism

For the same CV Version, Job Description, and matching-rule version, the MVP
must produce the same Match Report.

## 6. Safety, Privacy, and Guardrails

- The system must not invent experience, employers, metrics, awards, or skills.
- Missing Evidence must be shown as missing or weak, not silently upgraded.
- User approval is required before any later Patch changes a CV Version.
- Provider credentials, if added later, must be kept outside User-facing data
  and logs.
- The User must be able to identify whether content is original input,
  deterministic analysis, or later AI-generated proposal.

## 7. Non-Goals (Explicit)

- Automatic CV/DOCX/PDF parsing in MVP.
- LinkedIn import or synchronization.
- Automatic job applications.
- Recruiter messaging or employer workflows.
- Candidate ranking for employers.
- Full ATS scoring beyond the MVP's explainable skill/keyword signals.
- Automatic AI rewriting or silent CV mutation.
- Multi-agent orchestration before a measured need exists.
- Server-side/worker PDF generation in the initial export path.

## 8. MVP Scope

### 8.1 In Scope

- Web account access and ownership isolation.
- Structured CV Profile creation and editing.
- Mutable CV Profile plus immutable named CV Versions.
- Pasted Job Description intake and saved raw text.
- Deterministic Job Description analysis.
- Deterministic CV Version-to-Job Description Match Report.
- Explainable matched, missing, and Weak Evidence signals.
- At least one Template and a Preview.
- Browser print/HTML Export from a saved CV Version.
- Validation, failure states, and server-side authorization.

### 8.2 Out of Scope for MVP

- AI interview questions and AI-generated Patches; defer until the deterministic
  workflow is validated.
- Provider-specific LLM integration; defer to avoid making the first release
  dependent on external availability and cost.
- Background worker PDF generation; browser Export is sufficient for the first
  release.
- Advanced ATS optimization, review agents, and multi-agent orchestration;
  defer until measurable product need exists.
- Automatic document parsing and external profile integrations; defer because
  structured manual input is the current source workflow.

## 9. Later Phases

### Phase 3: AI-assisted revision

Add Evidence interview sessions, provider-neutral generation, Patch validation,
review, rejection, regeneration, and atomic approval into a new CV Version.

### Phase 4: Export expansion

Add additional Templates and server-side/queued Export only if browser Export
does not satisfy artifact requirements.

### Phase 5: Hardening and advanced AI

Add sanitized tool-call audit records, provider failure handling, retention and
deletion controls, operational metrics, ATS/review validators, and an
evidence-based decision on multi-agent orchestration.

## 10. Success Metrics

The initial release should establish a baseline before setting hard targets.

**Primary**

- **SM-1:** A User can complete the core flow from saved CV Profile to Match
  Report to Preview/Export without manual database or developer intervention.
  Validates FR-3, FR-4, FR-8, FR-10, and FR-11.
- **SM-2:** In evaluation fixtures, Match Reports are repeatable for unchanged
  inputs and clearly expose the evidence behind their result. Validates FR-8,
  FR-9, and NFR-5.

**Secondary**

- **SM-3:** Users can identify at least one actionable weak or missing area from
  a Match Report. Validates FR-9.
- **SM-4:** Exported output matches the selected CV Version and Template in
  manual acceptance checks. Validates FR-10, FR-11, and NFR-2.

**Counter-metrics**

- **SM-C1:** Do not optimize for a higher Match Report score by accepting
  unsupported claims; Evidence integrity and User trust take priority over
  score inflation. Counterbalances SM-2 and SM-3.
- **SM-C2:** Do not optimize for feature count by adding AI or integrations
  before the MVP core flow is reliable. Counterbalances delivery breadth.

## 11. Open Questions

1. What authentication policy and account recovery behavior are required for
   the first deployed environment?
2. What exact field-level limits and supported aliases belong in the initial
   structured CV contract?
3. What evaluation fixture and minimum repeatability/quality threshold should
   govern the deterministic matching rules?
4. Which initial Template visual direction and print layout should be approved?
5. What retention and deletion policy is required before storing later AI
   interview messages and tool-call metadata?
6. What evidence would justify adding server-side/worker PDF Export or
   multi-agent orchestration?

## 12. Assumptions Index

- **[ASSUMPTION: web MVP]** The MVP is delivered as a web experience because
  the existing repository contains a Vue web application and Laravel API.
- **[ASSUMPTION: manual JD input]** MVP Job Description input is pasted text;
  automatic document parsing is deferred.
- **[ASSUMPTION: one initial Template]** MVP needs at least one usable Template;
  the number and visual design remain open.
- **[ASSUMPTION: baseline metrics first]** Success Metrics begin with baseline
  measurement because no product usage or evaluation dataset exists yet.
