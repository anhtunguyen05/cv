# Domain Glossary

| Term | Meaning |
| --- | --- |
| User | Authenticated student, fresher, or job seeker who owns product data. |
| CV Profile | Mutable, structured source collection for a User's CV. |
| CV Version | Immutable named snapshot of a CV Profile. |
| Job Description | Logical saved target-role resource with raw input and revisions. |
| Job Description Revision | Immutable source state created when a Job Description changes. |
| Analysis | Deterministic interpretation of one exact Job Description Revision under an analysis-rule version. |
| Match Report | Explainable comparison of one CV Version and one exact Job Description Revision/Analysis. |
| Evidence | User-provided CV content supporting a claimed skill or capability. |
| Weak Evidence | A skill signal without sufficient supporting project or experience evidence. |
| Template | Presentation format used to render a saved CV Version. |
| Preview | Reviewable rendered CV Version plus Template. |
| Export | User-initiated artifact/copy of a reviewed Preview. |
| Patch | Post-MVP proposed, evidence-based structured change requiring explicit approval. |

Use these capitalized terms consistently in contracts, code, tests, and user
facing copy when referring to the product concept.
