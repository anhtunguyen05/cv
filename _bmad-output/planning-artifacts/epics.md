---
stepsCompleted: [1, 2, 3]
inputDocuments:
  - README.md
  - _bmad-output/planning-artifacts/prds/prd-CareerFitCV-2026-09-01/prd.md
  - _bmad-output/planning-artifacts/architecture/architecture-CareerFitCV-2026-09-01/ARCHITECTURE-SPINE.md
  - docs/implementation-plan.md
  - docs/implementation/README.md
  - docs/implementation/phase-0-foundation/README.md
  - docs/implementation/phase-0-foundation/github-issues.md
  - docs/implementation/phase-1-cv-versioning/README.md
  - docs/implementation/phase-1-cv-versioning/github-issues.md
  - docs/implementation/phase-2-jd-matching/README.md
  - docs/implementation/phase-2-jd-matching/github-issues.md
  - docs/implementation/phase-3-ai-patches/README.md
  - docs/implementation/phase-3-ai-patches/github-issues.md
  - docs/implementation/phase-4-preview-export/README.md
  - docs/implementation/phase-4-preview-export/github-issues.md
  - docs/implementation/phase-5-hardening/README.md
  - docs/implementation/phase-5-hardening/github-issues.md
  - docs/architecture.md
  - docs/ai-workflow.md
  - docs/database.md
  - docs/decisions.md
---

# CareerFitCV - Epic Breakdown

## Overview

This document provides the complete epic and story breakdown for CareerFitCV,
decomposing the canonical PRD, architecture spine, current implementation
plan, and phase issue packages into implementable stories.

## Requirements Inventory

### Functional Requirements

- FR1: The User can register, sign in, sign out, and retrieve their current account.
- FR2: The system restricts every User-owned resource to its owning User.
- FR3: The User can create and edit structured CV Profile sections.
- FR4: The User can create and select immutable named CV Version snapshots.
- FR5: The system preserves source integrity between CV Profile and CV Version.
- FR6: The User can create, view, update, and delete a Job Description.
- FR7: The system analyzes Job Description role, skill, responsibility, keyword, seniority, soft-skill, and domain signals.
- FR8: The User can generate a Match Report for one CV Version and one Job Description.
- FR9: The system explains matched skills, missing skills, and Weak Evidence.
- FR10: The User can render a CV Version with an active Template.
- FR11: The User can Export a reviewed CV from a Preview using browser print/HTML in MVP.
- FR12: Post-MVP, the system can propose an evidence-based Patch for User review.

### NonFunctional Requirements

- NFR1: Protect User-owned content with server-side privacy and authorization.
- NFR2: Preserve source identity, immutable Versions, and derived-data traceability.
- NFR3: Provide accessible loading, empty, validation, authorization, and failure states.
- NFR4: Avoid silent partial state and provide clear retryable or terminal failures.
- NFR5: Produce the same Match Report for the same inputs and matching-rule version.

### Additional Requirements

- Laravel owns trusted authentication, authorization, validation, persistence, and state transitions.
- Vue owns presentation and interaction state; it does not own trusted domain state.
- Use versioned structured CV documents; keep raw inputs separate from derived analysis.
- Use immutable Job Description revisions so derived results remain reproducible after edits or logical deletion.
- MVP matching is deterministic and does not require an LLM.
- Later AI providers return validated proposal data and cannot persist directly.
- Human approval is required before a Patch creates a new CV Version.
- Use one controlled orchestrator before considering multi-agent decomposition.
- The worker is optional and cannot become a second source of truth.
- Laravel health is `/api/health`; framework health is `/up`; worker health is separate.
- MVP Export starts with browser print/HTML; server-side/worker PDF is later.
- Current repository code is a scaffold; product domain features must not be treated as implemented until verified.

### UX Design Requirements

No UX design contract was found. UX stories must derive states and accessibility
checks from the PRD's journeys and NFR3 until a dedicated UX contract exists.

### FR Coverage Map

FR1: Epic 1 - Account access
FR2: Epic 1 - Ownership isolation
FR3: Epic 1 - Structured CV Profile
FR4: Epic 1 - Immutable CV Version
FR5: Epic 1 - Source integrity
FR6: Epic 2 - Job Description lifecycle
FR7: Epic 2 - Job Description analysis
FR8: Epic 2 - Match Report generation
FR9: Epic 2 - Evidence explanation
FR10: Epic 3 - Template Preview
FR11: Epic 3 - Browser Export
FR12: Epic 4 - Evidence-based AI Patch proposal

## Epic List

### Epic 1: Create and Manage a Trusted CV

Users can create an account, build a structured CV Profile, and preserve named
immutable CV Versions.

**FRs covered:** FR1, FR2, FR3, FR4, FR5

### Epic 2: Understand CV Fit for a Job

Users can save a Job Description, analyze its requirements, and compare it with
a selected CV Version through an explainable Match Report.

**FRs covered:** FR6, FR7, FR8, FR9

### Epic 3: Preview and Export an Application-Ready CV

Users can select a Template, preview a saved CV Version, and Export it through
the browser print/HTML path.

**FRs covered:** FR10, FR11

### Epic 4: Improve a CV with Evidence-Based AI Revision

Users can answer targeted Evidence questions and review AI-generated Patch
proposals before creating a new CV Version. This is post-MVP.

**FRs covered:** FR12

### Epic 5: Operate CareerFitCV Safely at Scale

Users and operators receive reliable, traceable behavior as asynchronous work,
AI integrations, retention, and monitoring are introduced.

**FRs covered:** NFR1, NFR2, NFR3, NFR4, NFR5

## Epic 1: Create and Manage a Trusted CV

Users can create an account, build a structured CV Profile, and preserve named
immutable CV Versions.

**FRs covered:** FR1, FR2, FR3, FR4, FR5

**Relevant architecture requirements:** Laravel owns authentication,
authorization, validation, and persistence; User-owned resources are isolated;
CV Profile is mutable; CV Version is immutable; structured CV data is the
canonical representation.

### Story 1.1: Register an account

As a prospective User,
I want to create an account with my name, email, and password,
So that I can securely store and manage my CV Profile.

**Acceptance Criteria:**

**Given** I am not authenticated
**When** I submit a valid name, unique email, and password
**Then** the system creates my User account
**And** I am authenticated as the newly created User
**And** my password is never returned in the response

**Given** the submitted email already belongs to a User
**When** I submit the registration form
**Then** the account is not created
**And** I receive a field-level validation error
**And** the existing User data is not disclosed

**Given** one or more required fields are missing or invalid
**When** I submit the registration form
**Then** the system rejects the request
**And** no partial User record is created
**And** validation errors identify the affected fields

**Given** I submit a password that does not meet the documented policy
**When** I submit the registration form
**Then** the system rejects the request
**And** the password policy is communicated without exposing sensitive details

**Given** registration succeeds
**When** I make a request for my current account
**Then** the response identifies my User
**And** it does not include the password or password hash

### Story 1.2: Sign in and sign out

As a returning User,
I want to sign in and sign out securely,
So that I can access my CV Profile while keeping my account protected.

**Acceptance Criteria:**

**Given** I have an existing User account
**When** I submit the correct email and password
**Then** the system authenticates me as that User
**And** I can access protected product capabilities
**And** the response does not expose my password or password hash

**Given** I submit an unknown email or incorrect password
**When** I submit the sign-in form
**Then** authentication fails
**And** I receive a generic authentication error
**And** the response does not reveal whether the email exists

**Given** I am authenticated
**When** I request my current account
**Then** the system returns my authenticated User
**And** the response excludes password and password hash fields

**Given** I am authenticated
**When** I sign out
**Then** the system invalidates my authenticated application state
**And** subsequent protected requests are rejected
**And** no User-owned data is modified or deleted

**Given** my authenticated state has expired or is invalid
**When** I request a protected capability
**Then** the system rejects the request
**And** the web application returns me to the sign-in state
**And** no protected data is displayed

### Story 1.3: Create a CV Profile

As an authenticated User,
I want to create a CV Profile with my basic personal information,
So that I have a structured starting point for my CV.

**Acceptance Criteria:**

**Given** I am authenticated
**When** I create a CV Profile with a valid title and personal information
**Then** the system creates the CV Profile for me
**And** the saved data can be retrieved after a page reload
**And** the CV Profile belongs only to me

**Given** I submit an empty or over-limit title
**When** I create the CV Profile
**Then** the system rejects the request
**And** a field-level validation error is returned
**And** no partial CV Profile is created

**Given** I submit invalid personal-information field types or values
**When** I create the CV Profile
**Then** the system rejects the invalid fields
**And** valid unrelated data is not persisted as a partial record

**Given** another User attempts to access the CV Profile by ID
**When** the request is made
**Then** the system does not disclose the CV Profile
**And** the CV Profile remains unchanged

**Given** I am using the CV Profile form with invalid or incomplete data
**When** validation fails
**Then** the relevant controls expose their errors in a keyboard-usable way
**And** the form preserves my valid entered values

### Story 1.4: Manage CV summary and skills

As an authenticated User,
I want to add a summary and categorized skills to my CV Profile,
So that my core qualifications are represented in a consistent structure.

**Acceptance Criteria:**

**Given** I own a CV Profile
**When** I save a valid summary and categorized skill entries
**Then** the system stores them as structured fields
**And** I can retrieve the same values after reloading
**And** optional empty skill categories do not prevent saving

**Given** a skill entry is empty, over-limit, or has an invalid type
**When** I save the CV Profile
**Then** the invalid entry is rejected with a field-level error
**And** the system does not silently normalize it into an unsupported value

**Given** I update the summary or skills on the mutable CV Profile
**When** I save the changes
**Then** the existing draft data is updated
**And** any previously saved CV Version remains unchanged

### Story 1.5: Manage education and experience

As an authenticated User,
I want to record my education and work experience as structured entries,
So that my background can later support CV comparison.

**Acceptance Criteria:**

**Given** I own a CV Profile
**When** I add valid education and experience entries
**Then** each entry is stored in its corresponding structured section
**And** each entry has a stable item identifier
**And** I can edit or remove an entry from the mutable CV Profile

**Given** an education or experience entry is missing required fields or contains invalid field types
**When** I save the CV Profile
**Then** the system rejects the invalid entry
**And** no malformed entry is persisted

**Given** I remove an education or experience entry from the CV Profile
**When** I save the change
**Then** the entry is no longer part of the current Profile
**And** any existing CV Version containing it remains unchanged

### Story 1.6: Manage projects

As an authenticated User,
I want to record projects with roles, technology, and bullet points,
So that my practical Evidence is distinguishable from a skills list.

**Acceptance Criteria:**

**Given** I own a CV Profile
**When** I add a valid project with a name and structured project details
**Then** the project is saved with a stable item identifier
**And** its technology and bullet-point fields can be retrieved independently
**And** the project is associated only with my CV Profile

**Given** a project has invalid fields, unsupported nested data, or over-limit bullet points
**When** I save the CV Profile
**Then** the system rejects the invalid project
**And** it does not persist a partial malformed project

**Given** I edit or remove a project in the mutable CV Profile
**When** I save the change
**Then** the current Profile reflects the change
**And** previously saved CV Versions are not changed

### Story 1.7: Manage supplementary CV sections

As an authenticated User,
I want to record certificates, languages, and activities,
So that my CV can represent relevant qualifications beyond core experience.

**Acceptance Criteria:**

**Given** I own a CV Profile
**When** I add valid certificates, languages, or activities
**Then** each entry is stored in its corresponding structured section
**And** each entry can be edited or removed from the mutable Profile

**Given** one supplementary entry is empty or has an invalid type
**When** I save the CV Profile
**Then** the invalid entry is rejected with a field-level error
**And** valid existing entries are not silently deleted

**Given** all supplementary sections are empty
**When** I save or view the CV Profile
**Then** the Profile remains valid
**And** the empty sections do not block later version creation or Preview

### Story 1.8: Create and view an immutable CV Version

As an authenticated User,
I want to create and view a named CV Version from my CV Profile,
So that I can preserve a stable CV snapshot for later comparison and Export.

**Acceptance Criteria:**

**Given** I own a valid CV Profile
**When** I create a CV Version with a valid name
**Then** the system stores a complete structured snapshot of the current Profile
**And** the CV Version has a stable identifier and creation timestamp
**And** I can open the Version after a page reload

**Given** I edit the source CV Profile after creating a CV Version
**When** I view the existing CV Version
**Then** the Version still contains its original snapshot
**And** the edited Profile contains only the new draft values

**Given** I own multiple CV Versions
**When** I list my Versions
**Then** the system returns only my Versions in reverse creation order
**And** each Version identifies its name and source Profile

**Given** I submit an empty or over-limit Version name
**When** I create a CV Version
**Then** the system rejects the request with a field-level validation error
**And** no invalid Version is created

**Given** another User attempts to read or create a Version using my Profile ID
**When** the request is made
**Then** the system denies the request without disclosing my Profile or Versions

## Epic 2: Understand CV Fit for a Job

Users can save a Job Description, analyze its requirements, and compare it with
a selected CV Version through an explainable Match Report.

**FRs covered:** FR6, FR7, FR8, FR9

### Story 2.1: Save a Job Description

As an authenticated User,
I want to save a Job Description for a target role,
So that I can compare one of my CV Versions with it later.

**Acceptance Criteria:**

**Given** I am authenticated
**When** I submit valid raw Job Description text with optional company and role information
**Then** the system saves a Job Description owned by me
**And** the original raw text is preserved
**And** I can retrieve the Job Description after reloading
**And** the Job Description has a stable identity with an initial immutable revision

**Given** the raw Job Description is empty or exceeds the supported input limit
**When** I submit it
**Then** the system rejects the request with a field-level validation error
**And** no partial Job Description is saved

**Given** another User attempts to access my Job Description
**When** the request is made
**Then** the system denies access without disclosing the resource

### Story 2.2: Manage saved Job Descriptions

As an authenticated User,
I want to update or delete my saved Job Description,
So that my target role information remains current.

**Acceptance Criteria:**

**Given** I own a saved Job Description
**When** I update its raw text or optional role information with valid data
**Then** the system creates a new immutable Job Description revision and makes it current
**And** the saved Job Description reflects the new revision
**And** its owning User remains unchanged

**Given** an earlier Job Description revision has an analysis or Match Report
**When** I update the Job Description
**Then** the earlier revision, analysis, and Match Report remain unchanged and reproducible
**And** the new revision has no successful analysis until I explicitly request analysis

**Given** I own a saved Job Description
**When** I delete it
**Then** the Job Description is logically deleted and no longer appears in active lists
**And** I cannot edit, analyze, or use it for a new comparison
**And** unrelated Job Descriptions remain unchanged

**Given** I own a Match Report generated from a deleted Job Description revision
**When** I open the Match Report
**Then** the report remains accessible with its original Job Description revision and analysis
**And** the system clearly identifies that the parent Job Description is deleted

### Story 2.3: Analyze a Job Description

As an authenticated User,
I want to see the requirements extracted from a Job Description,
So that I understand what the target role asks for before comparing my CV.

**Acceptance Criteria:**

**Given** I own a saved Job Description with valid raw text
**When** I request analysis
**Then** the system extracts available role, required skills, nice-to-have skills, responsibilities, keywords, seniority, soft skills, and domain/context signals
**And** it stores the analysis separately from the raw text
**And** the analysis identifies the exact Job Description revision and analysis-rule version used

**Given** the Job Description does not contain one of the supported signal types
**When** analysis completes
**Then** that signal is represented as absent or unknown
**And** the system does not invent a value

**Given** the same raw Job Description is analyzed with the same rule version
**When** analysis is repeated
**Then** the extracted result is repeatable

**Given** Job Description analysis cannot complete
**When** the failure is returned
**Then** the Job Description remains saved with its raw text intact
**And** I receive an actionable retry state
**And** no incomplete analysis is presented as successful

### Story 2.4: Generate a Match Report

As an authenticated User,
I want to compare a selected CV Version with a Job Description,
So that I can measure how well my saved CV fits the target role.

**Acceptance Criteria:**

**Given** I own a CV Version and a non-deleted Job Description whose current revision has a successful analysis
**When** I request a comparison
**Then** the system creates a Match Report using only that current revision
**And** the report contains an overall score, matched skills, missing skills, Weak Evidence, and recommendations
**And** the report stores the exact Job Description revision and analysis used
**And** the report stores the exact `analysis_id` used
**And** the report records the matching-rule version used

**Given** I own a non-deleted Job Description whose current revision has no successful analysis
**When** I request a new Match Report
**Then** the system rejects the request with an actionable state telling me to analyze the current revision first
**And** no partial Match Report is created

**Given** a Job Description has an older revision with a successful analysis and a newer current revision
**When** I request a new Match Report
**Then** the system does not reuse the older revision's analysis
**And** the request succeeds only after the current revision has its own successful analysis

**Given** I select a historical Job Description revision or a logically deleted Job Description for a new Match Report
**When** I request the comparison
**Then** the system rejects the request
**And** the historical revision remains readable only through its preserved analysis and existing Match Reports

**Given** the selected CV Version or Job Description belongs to another User
**When** I request a comparison
**Then** the system denies the request
**And** no Match Report is created

**Given** the CV Version and Job Description have not changed
**When** I repeat the comparison with the same matching-rule version
**Then** the report result is the same

### Story 2.5: Review an explainable Match Report

As an authenticated User,
I want to inspect why skills are matched, missing, or weak,
So that I can identify an actionable CV improvement area.

**Acceptance Criteria:**

**Given** I own a Match Report
**When** I open it
**Then** I can see the source CV Version and Job Description
**And** I can see the exact Job Description revision used to generate it
**And** I can distinguish matched skills, missing skills, Weak Evidence, and recommendations
**And** each available recommendation identifies its related CV section

**Given** a skill appears only in the CV skills list and has no supporting project or experience Evidence
**When** the report is displayed
**Then** the skill is presented as Weak Evidence rather than fully evidenced

**Given** the Match Report contains no match for a requested signal
**When** I view the report
**Then** the system does not replace the missing signal with an unsupported claim

**Given** I navigate the Match Report with a keyboard or assistive technology
**When** I review its score, skills, and recommendations
**Then** the content has meaningful labels and a usable reading/order sequence

## Epic 3: Preview and Export an Application-Ready CV

Users can select a Template, preview a saved CV Version, and Export it through
the browser print/HTML path.

**FRs covered:** FR10, FR11

### Story 3.1: Select a Template

As an authenticated User,
I want to choose an active Template for my CV Version,
So that I can view my CV in a presentation format suitable for an application.

**Acceptance Criteria:**

**Given** I am authenticated
**When** I request available Templates
**Then** the system returns only active Templates available to me
**And** each Template has enough information for me to select it

**Given** a Template is inactive or unavailable
**When** I attempt to select it
**Then** the system rejects the selection
**And** no Preview or Export is created from that Template

### Story 3.2: Preview a saved CV Version

As an authenticated User,
I want to preview a saved CV Version using a Template,
So that I can review its layout before exporting it.

**Acceptance Criteria:**

**Given** I own a saved CV Version and have selected an active Template
**When** I open the Preview
**Then** the system renders the CV Version using that Template
**And** all supported non-empty sections are visible
**And** empty optional sections do not break the Preview

**Given** the CV Profile has unsaved changes after the CV Version was created
**When** I open the Preview
**Then** the Preview uses the saved CV Version snapshot
**And** it does not display unsaved draft values

**Given** CV content contains markup-like characters
**When** the Preview renders
**Then** the content is displayed as text or safely formatted data
**And** it does not execute as markup or script

**Given** I navigate the Preview with a keyboard or assistive technology
**When** I review the rendered CV
**Then** headings, sections, and interactive controls have a meaningful accessible structure

### Story 3.3: Export a reviewed CV

As an authenticated User,
I want to Export the CV Version I reviewed,
So that I can submit an application-ready copy.

**Acceptance Criteria:**

**Given** I own a saved CV Version and have a valid Preview
**When** I initiate an Export
**Then** the browser print/HTML Export uses the selected CV Version and Template
**And** the output does not include unsaved Profile values
**And** the Export action does not require an AI provider

**Given** Export cannot complete
**When** the failure is returned
**Then** I see an actionable error
**And** I can retry without creating duplicate unexplained state

**Given** another User attempts to Export my CV Version
**When** the request is made
**Then** the system denies the request
**And** no artifact from my CV Version is exposed

## Epic 4: Improve a CV with Evidence-Based AI Revision

Users can answer targeted Evidence questions and review AI-generated Patch
proposals before creating a new CV Version. This is post-MVP.

**FRs covered:** FR12

### Story 4.1: Start an Evidence interview

As an authenticated User,
I want to start an Evidence interview from a Match Report,
So that I can provide missing details before requesting a CV improvement.

**Acceptance Criteria:**

**Given** I own a Match Report with missing or Weak Evidence areas
**When** I start an Evidence interview
**Then** the system creates an interview session associated with that Match Report, CV Version, and Job Description
**And** the session identifies the areas requiring Evidence
**And** the source CV Version remains unchanged

**Given** the Match Report has no unresolved improvement area
**When** I attempt to start an interview
**Then** the system explains that no interview is currently needed
**And** it does not create a misleading session

### Story 4.2: Answer Evidence questions

As an authenticated User,
I want to submit answers to targeted Evidence questions,
So that the system can distinguish my real experience from unsupported claims.

**Acceptance Criteria:**

**Given** I own an active Evidence interview
**When** I submit a valid answer to the current question
**Then** the answer is stored with its session and timestamp
**And** the original answer remains distinguishable from system-generated content

**Given** I submit an empty or over-limit answer
**When** I submit it
**Then** the system rejects it with an actionable validation error
**And** no empty answer is stored as Evidence

**Given** I cannot provide Evidence for a question
**When** I indicate that I do not know or did not perform the activity
**Then** the session records that outcome
**And** the system does not treat it as supporting Evidence

### Story 4.3: Generate a Patch proposal

As an authenticated User,
I want the system to propose a structured Patch from my Evidence,
So that I can review a targeted CV improvement.

**Acceptance Criteria:**

**Given** I own an Evidence interview with submitted answers
**When** I request Patch generation
**Then** the system returns a Patch proposal identifying the section, field, old value, new value, reason, and Evidence sources
**And** the proposal is stored as pending review
**And** the source CV Version is unchanged

**Given** the provider returns unsupported fields, missing Evidence, or malformed output
**When** Patch generation completes
**Then** the system rejects the proposal
**And** no unvalidated Patch is stored as pending
**And** the failure is recorded without exposing provider secrets

### Story 4.4: Review, edit, or reject a Patch

As an authenticated User,
I want to inspect and decide on a Patch proposal,
So that I remain in control of changes to my CV.

**Acceptance Criteria:**

**Given** I own a pending Patch
**When** I open it
**Then** I can see the old value, new value, reason, and Evidence sources
**And** I can distinguish the proposal from the current CV Version

**Given** I edit a proposed value within the permitted Patch contract
**When** I save the edit
**Then** the edited proposal remains pending validation
**And** the current CV Version is unchanged

**Given** I reject a pending Patch
**When** I confirm rejection
**Then** the Patch status becomes rejected
**And** the source CV Version remains unchanged

### Story 4.5: Approve a Patch into a new CV Version

As an authenticated User,
I want to approve a validated Patch,
So that the improvement becomes a new CV Version without overwriting history.

**Acceptance Criteria:**

**Given** I own a valid pending Patch whose source CV Version has not changed
**When** I explicitly approve it
**Then** the system validates the Patch again
**And** creates a new CV Version containing the approved change
**And** marks the Patch as applied
**And** leaves the source CV Version unchanged

**Given** the Patch old value no longer matches the source CV Version
**When** I approve it
**Then** the system rejects the approval as a stale conflict
**And** no new CV Version is created
**And** the Patch is not marked applied

**Given** the approval operation fails during persistence
**When** the failure is returned
**Then** the source CV Version and Patch status remain consistent
**And** I receive an actionable retry outcome

### Story 4.6: Regenerate a rejected or invalid Patch

As an authenticated User,
I want to regenerate a Patch after reviewing its failure or rejection,
So that I can try a corrected proposal without losing the prior decision.

**Acceptance Criteria:**

**Given** I own a rejected or invalid Patch and its Evidence context is still available
**When** I request regeneration
**Then** the system creates a new pending proposal or reports why regeneration is unavailable
**And** the previous Patch remains in its historical state
**And** the source CV Version remains unchanged

**Given** the Evidence context is no longer valid for the source CV Version
**When** I request regeneration
**Then** the system refuses to generate a proposal from stale context
**And** it explains the next required action

## Epic 5: Operate CareerFitCV Safely at Scale

Users and operators receive reliable, traceable behavior as asynchronous work,
AI integrations, retention, and monitoring are introduced.

**FRs covered:** NFR1, NFR2, NFR3, NFR4, NFR5

### Story 5.1: Audit AI tool calls safely *(post-MVP hardening)*

As an operator,
I want sanitized AI tool-call metadata,
So that I can diagnose provider behavior without exposing User content or secrets.

**Acceptance Criteria:**

**Given** an AI tool call is made
**When** it completes or fails
**Then** the system records tool, provider, model, status, duration, and failure category
**And** credentials and unnecessary raw CV/JD content are excluded or redacted

**Given** an operator views an audit record
**When** the record is displayed
**Then** it is distinguishable from trusted product state
**And** it cannot be used to modify a CV Version directly

### Story 5.2: Handle provider and background failures *(post-MVP hardening)*

As a User,
I want provider and background failures to produce clear outcomes,
So that a temporary failure does not create a misleading CV result.

**Acceptance Criteria:**

**Given** a provider times out, rate-limits, or returns malformed data
**When** the operation fails
**Then** the system applies bounded retry behavior where safe
**And** the User sees an actionable failure state
**And** no unvalidated Patch or partial trusted state is created

**Given** an asynchronous Export or analysis job fails
**When** I view its status
**Then** the status is terminal or retryable according to the documented lifecycle
**And** it does not remain falsely marked as successful

### Story 5.3: Retain and delete User data safely *(post-MVP hardening)*

As a User,
I want my stored content and generated artifacts to follow a clear retention and deletion policy,
So that I can control the lifecycle of my career data.

**Acceptance Criteria:**

**Given** the documented retention period is reached
**When** the retention process runs
**Then** eligible messages, tool-call records, and artifacts are handled according to policy
**And** retained trusted records remain internally consistent

**Given** I request deletion of eligible User data
**When** deletion completes
**Then** the requested content and dependent non-authoritative records are removed or anonymized according to policy
**And** unrelated Users' data is unchanged
**And** the deletion result is auditable without retaining unnecessary sensitive content

### Story 5.4: Monitor operational health *(post-MVP hardening)*

As an operator,
I want metrics for matching, provider, Patch, Export, and system failures,
So that I can identify regressions without inspecting User content.

**Acceptance Criteria:**

**Given** a supported product operation completes or fails
**When** its telemetry is recorded
**Then** the system captures the defined latency, status, and failure metrics
**And** the metrics do not contain raw User CV or Job Description content

### Story 5.5: Validate deterministic matching quality *(MVP baseline)*

As an operator,
I want deterministic Match Reports evaluated against fixtures,
So that matching regressions are detected without changing User data.

**Acceptance Criteria:**

**Given** a deterministic Match Report is evaluated against a fixture
**When** the validator runs
**Then** it reports repeatability and rule-version results
**And** it does not silently change the stored Match Report

### Story 5.6: Decide whether multi-agent orchestration is justified *(post-MVP hardening)*

As an architecture owner,
I want an evidence-based decision about multi-agent decomposition,
So that added routing and ownership complexity is justified by a measured limitation.

**Acceptance Criteria:**

**Given** the single orchestrator has been measured against current workloads
**When** the architecture decision is reviewed
**Then** the result records the observed limitation or the decision to defer
**And** any proposed agents preserve the existing tool, validation, ownership, and human-approval boundaries

**Given** no measured limitation justifies multiple agents
**When** the review completes
**Then** no multi-agent component is added
**And** the deferral condition is recorded for future review

### Story 5.7: Verify the operational safety baseline *(post-MVP hardening)*

As an operator,
I want a verified safety and operations baseline,
So that the system can be maintained without guessing about failures or data handling.

**Acceptance Criteria:**

**Given** the hardening stories are complete
**When** the operational verification is run
**Then** provider failure, redaction, retention, deletion, telemetry, and deterministic validation checks pass
**And** unresolved production decisions are explicitly listed
**And** the architecture decision record for multi-agent work is available

## Story Planning Assumptions

- MVP authentication creates an authenticated application state after registration; exact browser session/token mechanics remain implementation-level.
- Job Description edits create immutable revisions; analysis and Match Reports pin a revision, while deletion is logical and preserves historical reports for the owning User.
- Initial CV field limits, skill aliases, matching weights, evaluation fixtures, and Template visual direction are defined before the stories that consume them are implemented.
- Preview may be rendered in the web client if it consumes only saved CV Version data and cannot mutate trusted state.
- Post-MVP AI stories use a fake provider in tests; no provider/model is selected by these stories.
- Retention periods, deletion semantics, telemetry thresholds, and asynchronous job lifecycle are unresolved until Epic 5 planning.
