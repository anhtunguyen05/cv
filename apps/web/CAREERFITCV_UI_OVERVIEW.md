# CareerFitCV --- UI Overview

> High-level UI direction for the entire CareerFitCV frontend.\
> This document defines the shared visual language and implementation
> principles for the project. It is intentionally an **overview**, not a
> page-by-page specification.

------------------------------------------------------------------------

## 1. UI Vision

CareerFitCV should feel like a **modern, trustworthy, technical SaaS
product** for creating and tailoring professional CVs.

The interface combines two visual contexts:

-   **Marketing experience** --- confident, polished, spacious, visually
    strong.
-   **Application workspace** --- light, structured, information-dense,
    efficient.

The product should communicate:

-   Precision
-   Trust
-   Professionalism
-   Transparency
-   Technical credibility
-   AI assistance without an overly futuristic or gimmicky appearance

### Keywords

`Modern SaaS` · `Professional` · `Technical` · `Clean` ·
`Evidence-based` · `Precise` · `Minimal`

------------------------------------------------------------------------

## 2. Overall Visual Direction

The project uses a predominantly **light application UI** combined with
selective **dark presentation surfaces**.

### Marketing surfaces

Use:

-   Dark navy backgrounds
-   Large white typography
-   Indigo/violet primary actions
-   Subtle gradients
-   Product screenshots/previews as major visual elements
-   Generous whitespace

### Application surfaces

Use:

-   White or very light gray backgrounds
-   Thin cool-gray borders
-   Compact navigation
-   Clear hierarchy
-   Moderate information density
-   Indigo/violet for actions and selections
-   Green for verified/success states

The application should look like a professional productivity tool rather
than a dashboard full of decorative widgets.

------------------------------------------------------------------------

## 3. Color Direction

Use colors semantically and consistently.

### Primary

**Indigo / violet**

Used for:

-   Primary CTA
-   Active navigation
-   Selected template
-   Links
-   Interactive emphasis
-   Focus state
-   Brand accents

Suggested range:

``` text
#6366F1
#4F46E5
#4338CA
```

### Neutral

Use cool slate/gray tones for the majority of the interface.

``` text
Background       #FFFFFF
Subtle surface   #F8FAFC
Border           #E2E8F0
Muted text       #64748B
Primary text     #0F172A
```

### Dark surfaces

Dark navy should be preferred over pure black.

``` text
#252D45
#20273C
#1E293B
```

### Success / verified

Green represents:

-   Verified evidence
-   Successful validation
-   Compatibility
-   Positive status
-   Completed checks

``` text
#10B981
#059669
#ECFDF5
```

### Warning and destructive states

Amber:

``` text
Warnings
Attention-required states
```

Red:

``` text
Errors
Invalid states
Destructive actions
Rejected/removed information
```

### Principle

**Color must communicate meaning.**

Avoid introducing decorative colors without a semantic role.

------------------------------------------------------------------------

## 4. Typography

Use a clean sans-serif font across the product.

Recommended:

``` text
Inter
```

Fallback:

``` css
ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif
```

A monospace font may be used selectively for:

-   Hashes
-   IDs
-   Audit metadata
-   Technical status
-   Machine-generated references

Recommended:

``` text
JetBrains Mono
```

### Typography character

Headings:

``` text
Clear
Confident
Medium-to-bold weight
Compact line-height
```

Body:

``` text
Highly readable
Neutral
Concise
```

Application metadata:

``` text
Smaller
Muted
Compact
```

Avoid oversized typography inside the application workspace.

------------------------------------------------------------------------

## 5. Layout Philosophy

The layout should prioritize **content clarity before decoration**.

Use a consistent centered application container where appropriate:

``` text
max-width: approximately 1200–1440px
```

Marketing pages can use wider visual sections.

Application pages should generally follow:

``` text
Global Navigation
       ↓
Page Header / Context
       ↓
Primary Workspace
       ↓
Persistent or contextual actions
```

### Workspace layouts

Prefer:

``` text
Single column
Two-column comparison
Sidebar + content
Master/detail
Document + controls
```

depending on the task.

Avoid arbitrary dashboard grids when the workflow is naturally
sequential.

------------------------------------------------------------------------

## 6. Navigation

Navigation should remain lightweight and predictable.

### Global navigation

Typical structure:

``` text
Brand
Primary product navigation
Secondary/help actions
User account
```

Marketing and authenticated application navigation may differ slightly,
but they should share:

-   Logo
-   Typography
-   Button language
-   Color system
-   Spacing rhythm

### Active state

Use indigo/violet emphasis.

Do not rely only on text weight to indicate the current location.

------------------------------------------------------------------------

## 7. Surfaces

The UI should use a small number of surface levels.

``` text
Page background
    ↓
Primary surface
    ↓
Card / panel
    ↓
Interactive or highlighted region
```

### Cards

Cards should generally use:

``` text
White background
1px subtle border
Small/moderate radius
Minimal shadow
```

Shadows should communicate elevation, not decoration.

### Highlighted information

Use tinted backgrounds such as:

``` text
Indigo tint → selected / AI / primary information
Green tint  → verified / successful
Amber tint  → warning
Red tint    → error / rejected
Gray tint   → neutral / disabled / metadata
```

------------------------------------------------------------------------

## 8. Shape and Borders

The design should feel modern but not overly playful.

Recommended radius philosophy:

``` text
Small controls     6px
Buttons / inputs   6–8px
Cards              8–12px
Large overlays     12–16px
```

Use pills primarily for:

-   Tags
-   Status badges
-   Small filters

Avoid making every control fully rounded.

Borders should usually be:

``` text
1px
cool gray
low contrast
```

------------------------------------------------------------------------

## 9. Spacing

Use a consistent **4px-based spacing system**.

Core values:

``` text
4
8
12
16
20
24
32
40
48
64
80
96
```

Application interfaces should generally be more compact than marketing
sections.

Avoid arbitrary spacing values unless there is a strong layout reason.

------------------------------------------------------------------------

## 10. Interaction Style

Interactions should feel immediate and restrained.

Every interactive component should provide appropriate:

``` text
Default
Hover
Focus
Active
Disabled
Loading
Error
```

states.

### Motion

Use subtle transitions:

``` text
100–250ms
```

Appropriate for:

-   Buttons
-   Tabs
-   Dropdowns
-   Dialogs
-   Tooltips
-   Selection
-   Progress updates

Avoid decorative animation that distracts from document work.

------------------------------------------------------------------------

## 11. Document-Centric UI

The CV/document is one of the product's primary objects.

Document-related interfaces should therefore preserve a strong
distinction between:

``` text
Application chrome
vs.
Actual document
```

The CV preview should feel like a real document:

-   White paper surface
-   Clear page boundary
-   Realistic page proportions
-   Subtle shadow
-   Neutral surrounding canvas

The surrounding UI should not visually compete with the document.

For multi-page previews, pages should remain clearly separated.

------------------------------------------------------------------------

## 12. Selection and Comparison

The product contains workflows where users choose between alternatives
such as templates, versions, or generated results.

Selection should be immediately visible through a combination of:

``` text
Border
Background tint
Check/status indicator
Primary action state
```

The selected item should always be unambiguous.

Comparison interfaces should keep repeated information visually aligned
to reduce scanning effort.

------------------------------------------------------------------------

## 13. Status and Trust Language

Trust is a major part of the product identity.

Use consistent visual treatment for:

``` text
Verified
Grounded
Matched
Compatible
Saved
Generated
Processing
Failed
Warning
```

Status should combine:

``` text
Icon + label + semantic color
```

where appropriate.

Do not communicate important status using color alone.

Technical metadata can use compact labels and monospace typography.

------------------------------------------------------------------------

## 14. Responsive Behavior

The UI must be designed for:

``` text
Desktop
Tablet
Mobile
```

Desktop is the primary workspace experience, especially for document
editing and preview.

### Responsive principle

Do not simply shrink desktop layouts.

Instead:

``` text
Two columns → stacked
Dense toolbar → condensed toolbar/menu
Side controls → drawer/dialog
Large preview → viewport-width preview
Secondary metadata → progressively hidden/collapsed
```

Primary actions must remain easy to reach on smaller screens.

------------------------------------------------------------------------

## 15. Accessibility

Accessibility is part of the component design, not an afterthought.

The UI should target at least WCAG AA for primary flows.

Requirements include:

-   Semantic HTML
-   Keyboard navigation
-   Visible focus states
-   Correct input labels
-   Sufficient contrast
-   Accessible dialogs and menus
-   Screen-reader-friendly status
-   Reduced-motion support
-   No color-only communication

------------------------------------------------------------------------

## 16. Frontend UI Stack

Recommended UI foundation:

``` text
Vue 3
TypeScript
Vite
Tailwind CSS
Reka UI
Lucide Vue Next
```

### Vue 3

Use:

``` text
Composition API
<script setup lang="ts">
```

### Tailwind CSS

Primary visual implementation layer for:

``` text
Layout
Spacing
Typography
Colors
Responsive behavior
Interaction states
```

### Reka UI

Use for accessible headless behavior:

``` text
Dialog
Dropdown
Popover
Tooltip
Select
Tabs
Accordion
Checkbox
Radio
Switch
Navigation primitives
```

Reka UI provides **behavior and accessibility**, not the visual
identity.

### Lucide Vue Next

Use as the default icon system.

Avoid mixing multiple icon libraries unless required.

------------------------------------------------------------------------

## 17. Component Strategy

The project should maintain its own UI layer.

``` text
Reka UI
   ↓
Shared UI Components
   ↓
Feature Components
   ↓
Pages
```

Suggested conceptual structure:

``` text
src/
├── components/
│   ├── ui/
│   └── common/
│
├── features/
│   ├── cv/
│   ├── templates/
│   ├── matching/
│   └── ...
│
├── layouts/
├── pages/
└── assets/
    └── styles/
```

### `components/ui`

Contains reusable, domain-agnostic components such as:

``` text
Button
Input
Card
Badge
Dialog
Dropdown
Tooltip
Tabs
Select
Progress
Skeleton
```

### `features/*`

Contains product-specific components.

Examples:

``` text
CvPreview
TemplateCard
FitScore
EvidencePanel
JobRequirement
```

Do not put business-specific components into the generic UI layer.

------------------------------------------------------------------------

## 18. Design Tokens

Prefer semantic tokens over raw values scattered throughout components.

Conceptually:

``` text
primary
primary-hover

background
surface
surface-muted

text
text-muted

border

success
warning
danger

focus
```

This allows the visual system to evolve without rewriting individual
feature components.

------------------------------------------------------------------------

## 19. Consistency Rules

Across the entire product:

-   Use one primary brand color.
-   Use one icon family.
-   Use one spacing system.
-   Use one radius system.
-   Use one typography system.
-   Keep status colors semantic.
-   Keep shadows subtle.
-   Keep application UI lighter and denser than marketing UI.
-   Use dark backgrounds selectively, not everywhere.
-   Prefer borders and whitespace over excessive card shadows.
-   Prefer reusable UI primitives over page-specific styling.
-   Keep the CV/document visually dominant whenever it is the focus of
    the workflow.

------------------------------------------------------------------------

## 20. Avoid

Avoid turning the product into:

``` text
A generic admin dashboard
A Material Design application
A heavily animated AI interface
A neon/cyberpunk AI product
A UI where every section is a card
A UI with excessive gradients
A UI with excessive rounded pills
```

Also avoid using a heavily opinionated styled component framework as the
primary design system unless product requirements later justify it.

------------------------------------------------------------------------

## 21. Final UI Identity

CareerFitCV should visually sit between:

``` text
Professional document software
        +
Modern SaaS application
        +
Technical AI tooling
```

The UI should be polished enough for a commercial SaaS product while
remaining restrained enough that the user's **CV, evidence, matching
results, and decisions remain the visual focus**.

### Core implementation principle

> **Tailwind controls presentation. Reka UI controls accessible
> interaction behavior. Shared UI components define the CareerFitCV
> design language. Feature components express product-specific
> workflows.**

### Core visual principle

> **Professional first, technical second, AI third.**

The interface should communicate intelligence through clarity, evidence,
and workflow quality rather than through excessive visual effects.
