# CareerFit AI

**CareerFit AI** is a CV and Job Description matching platform that helps students, freshers, and job seekers tailor their CV for a specific job. The system analyzes a user's CV and a target Job Description, identifies skill gaps, asks follow-up questions to collect missing information, and generates structured CV revision patches that can be reviewed and applied directly to a selected CV template.

> Core idea: **CV + Job Description → Match Report → AI Follow-up Questions → Structured CV Patch → Template Preview → Export PDF**

---

## 1. Problem Statement

Students and freshers often struggle to adjust their CV for each job application. Common issues include:

- CV descriptions are too generic.
- Important keywords from the Job Description are missing.
- Projects are not written with enough technical evidence.
- Skills are listed but not supported by project experience.
- Users do not know which parts of their CV should be improved.
- Existing AI tools often generate text suggestions separately instead of updating the CV structure safely.

CareerFit AI solves this by combining **CV parsing, JD analysis, skill matching, LLM tool calling, human-in-the-loop revision, and template-based CV editing**.

---

## 2. Project Goals

CareerFit AI aims to provide:

1. A structured CV builder for students and freshers.
2. A Job Description analyzer that extracts required skills, responsibilities, and keywords.
3. A CV-JD matching report with score, missing skills, weak sections, and recommendations.
4. An AI interview flow that asks the user for missing details before rewriting the CV.
5. A patch-based CV editing system where users can accept or reject AI suggestions.
6. CV versioning for different job applications.
7. Template-based CV preview and PDF export.

---

## 3. Main Features

### 3.1 CV Profile Builder

Users can create a structured CV profile including:

- Personal information
- Summary
- Education
- Skills
- Projects
- Work experience
- Certificates
- Languages
- Activities

The CV is stored as structured JSON instead of raw text. This makes it easier for the AI to update specific sections safely.

### 3.2 Job Description Analyzer

Users paste a Job Description into the system. The system extracts:

- Job title
- Company name
- Required skills
- Nice-to-have skills
- Responsibilities
- Keywords
- Seniority level
- Soft skills
- Domain/context

Example output:

```json
{
  "role": "ReactJS Intern",
  "level": "intern",
  "required_skills": ["React", "TypeScript", "REST API", "Git"],
  "nice_to_have": ["Redux", "Next.js", "TailwindCSS"],
  "responsibilities": [
    "Build UI components",
    "Integrate REST APIs",
    "Collaborate with backend team"
  ]
}
```

### 3.3 CV-JD Matching

The system compares the CV with the Job Description and generates:

- Overall match score
- Matched skills
- Missing skills
- Weak evidence areas
- ATS keyword coverage
- Section-level recommendations

Example:

```txt
Overall Match: 72%
React: matched
TypeScript: weak evidence
REST API: matched
Git: missing from CV
Team collaboration: weak evidence
Redux: missing
```

### 3.4 AI Follow-up Interview

If the AI detects that some CV sections are weak or missing evidence, it asks the user targeted follow-up questions.

Example:

```txt
Your Edura project mentions React, but the target JD requires TypeScript and REST API integration.

1. Did you use TypeScript in this project?
2. Which screens or flows did you directly implement?
3. Did you integrate REST APIs using Axios, fetch, or RTK Query?
4. Did you work in a team using Git/GitLab/GitHub?
```

The user's answers are then used as evidence for CV rewriting.

### 3.5 Structured CV Patch System

Instead of directly overwriting the CV, the AI generates structured patches.

Example patch:

```json
{
  "section": "projects",
  "item_id": "edura",
  "field": "bullets",
  "operation": "replace",
  "old_value": [
    "Built an online tutoring platform."
  ],
  "new_value": [
    "Developed tutor detail, booking form, authentication, and payment success pages using React and TypeScript.",
    "Integrated REST APIs generated from Swagger with RTK Query to handle booking, authentication, and payment confirmation flows.",
    "Collaborated in a 4-member team using GitLab for version control and issue tracking."
  ],
  "reason": "Added stronger evidence for React, TypeScript, REST API integration, and teamwork based on the target JD."
}
```

Users can:

- Accept patch
- Reject patch
- Edit patch manually
- Regenerate patch
- Save as a new CV version

### 3.6 Template-based CV Preview

The selected CV template renders data from the structured CV JSON.

Flow:

```txt
CV JSON → Vue Template Renderer → Preview → Export PDF
```

This avoids directly editing PDF/DOCX files, which is harder and more error-prone.

### 3.7 CV Versioning

Users can create multiple CV versions for different jobs:

```txt
CV - ReactJS Intern - Company A
CV - Frontend Intern - Company B
CV - Fullstack Intern - Company C
```

Each version keeps its own:

- CV data
- Template
- Target JD
- Match report
- AI patches
- Export history

---

## 4. Recommended Tech Stack

### Frontend

- Vue 3
- TypeScript
- Vue Router
- Pinia
- TailwindCSS
- shadcn-vue or PrimeVue
- Markdown editor or rich text editor
- HTML/CSS CV templates

### Backend

- Laravel
- Laravel Sanctum
- MySQL or PostgreSQL
- Laravel Queue
- Laravel Scheduler
- Laravel Storage
- Laravel Notifications

### AI Layer

- LLM provider: OpenAI, Gemini, OpenRouter, or local model
- Tool registry inside Laravel
- Prompt templates
- JSON Schema validation
- AI tool call logs
- Human-in-the-loop approval

### Optional Services

- Redis for queue/cache
- Node/Puppeteer service for PDF export
- Docker and Docker Compose
- VPS deployment with Nginx, PHP-FPM, Supervisor, and SSL

---

## 5. Monorepo Structure

Recommended structure:

```txt
careerfit-ai/
├── apps/
│   ├── web/                    # Vue 3 frontend
│   ├── api/                    # Laravel backend
│   └── worker/                 # Optional Node/Python worker for PDF/CV parsing
│
├── packages/
│   ├── shared-schemas/         # JSON schemas for CV, JD, patches
│   ├── prompts/                # AI prompt templates
│   └── cv-templates/           # Shared template config/assets
│
├── infra/
│   ├── nginx/                  # Nginx config for VPS
│   ├── supervisor/             # Queue worker config
│   └── deploy/                 # Deployment scripts
│
├── docs/
│   ├── architecture.md
│   ├── database.md
│   ├── ai-workflow.md
│   └── api.md
│
├── docker-compose.yml
├── README.md
└── .gitignore
```

A simpler version is also acceptable:

```txt
careerfit-ai/
├── frontend/       # Vue frontend
├── backend/        # Laravel backend
├── shared/         # JSON schemas and prompts
├── infra/          # Deployment config
└── docs/           # Documentation
```

---

## 6. High-level Architecture

```txt
[Vue Frontend]
  ├── CV Builder
  ├── JD Input
  ├── Match Report
  ├── AI Interview Chat
  ├── Patch Review UI
  ├── CV Template Preview
  └── PDF Export

        ↓ REST API

[Laravel Backend]
  ├── Auth Module
  ├── CV Profile Module
  ├── Job Description Module
  ├── Matching Module
  ├── AI Orchestrator Module
  ├── Tool Registry
  ├── Patch Module
  ├── Versioning Module
  └── Export Module

        ↓

[AI Tools]
  ├── parse_cv
  ├── analyze_jd
  ├── compare_cv_jd
  ├── identify_missing_info
  ├── generate_follow_up_questions
  ├── generate_cv_patch
  ├── validate_cv_patch
  └── apply_cv_patch
```

---

## 7. AI Workflow

### 7.1 CV Optimization Flow

```txt
1. User creates or imports a CV profile.
2. User selects a CV template.
3. User pastes a Job Description.
4. System analyzes the Job Description.
5. System compares the CV with the JD.
6. System generates a match report.
7. AI identifies weak or missing sections.
8. AI asks follow-up questions.
9. User answers the questions.
10. AI generates structured CV patches.
11. User reviews and accepts/rejects patches.
12. Template preview updates automatically.
13. User exports the final CV.
```

### 7.2 LLM Tool Calling

The LLM should not directly modify the database. It should call controlled backend tools.

Example tools:

```txt
get_cv_profile(cv_id)
analyze_job_description(jd_id)
compare_cv_to_jd(cv_id, jd_id)
identify_weak_sections(match_report_id)
generate_follow_up_questions(weak_sections)
generate_cv_patch(cv_id, jd_id, user_answers)
validate_cv_patch(patch_json)
apply_cv_patch(cv_version_id, patch_id)
```

### 7.3 Human-in-the-loop Rule

AI-generated patches are never applied automatically.

The user must review and approve each patch before it changes the CV version.

---

## 8. Multi-agent Design Optional

CareerFit AI can start with a single AI orchestrator. Later, it can be extended into multiple agents:

```txt
CareerFit Coordinator Agent
├── CV Parser Agent
├── JD Analyst Agent
├── Skill Gap Agent
├── Interview Agent
├── CV Rewrite Agent
├── ATS Optimization Agent
└── Review Agent
```

### Agent Responsibilities

| Agent | Responsibility |
|---|---|
| CV Parser Agent | Converts raw CV data into structured JSON |
| JD Analyst Agent | Extracts job requirements and keywords |
| Skill Gap Agent | Compares CV evidence with JD requirements |
| Interview Agent | Asks follow-up questions for missing evidence |
| CV Rewrite Agent | Generates stronger CV bullets and summaries |
| ATS Optimization Agent | Checks keyword coverage and readability |
| Review Agent | Prevents unsupported or exaggerated claims |

---

## 9. Database Design Draft

### users

```txt
id
name
email
password
created_at
updated_at
```

### cv_profiles

```txt
id
user_id
title
base_data_json
created_at
updated_at
```

### cv_versions

```txt
id
cv_profile_id
job_description_id nullable
template_id
version_name
data_json
match_score
created_at
updated_at
```

### job_descriptions

```txt
id
user_id
company_name
job_title
raw_text
analysis_json
created_at
updated_at
```

### match_reports

```txt
id
cv_version_id
job_description_id
score
matched_skills_json
missing_skills_json
weak_sections_json
recommendations_json
created_at
updated_at
```

### ai_interview_sessions

```txt
id
user_id
cv_version_id
job_description_id
status
created_at
updated_at
```

### ai_interview_messages

```txt
id
session_id
role             # user | assistant
content
metadata_json
created_at
```

### cv_patches

```txt
id
cv_version_id
job_description_id
status           # pending | accepted | rejected
patch_json
reason
created_at
applied_at nullable
```

### templates

```txt
id
name
type
preview_image
config_json
is_active
created_at
updated_at
```

### ai_tool_calls

```txt
id
user_id
session_id nullable
tool_name
input_json
output_json
status
error_message
duration_ms
created_at
```

---

## 10. API Endpoints Draft

### Auth

```txt
POST   /api/auth/register
POST   /api/auth/login
POST   /api/auth/logout
GET    /api/auth/me
```

### CV Profiles

```txt
GET    /api/cv-profiles
POST   /api/cv-profiles
GET    /api/cv-profiles/{id}
PUT    /api/cv-profiles/{id}
DELETE /api/cv-profiles/{id}
```

### CV Versions

```txt
GET    /api/cv-profiles/{id}/versions
POST   /api/cv-profiles/{id}/versions
GET    /api/cv-versions/{id}
PUT    /api/cv-versions/{id}
POST   /api/cv-versions/{id}/export-pdf
```

### Job Descriptions

```txt
GET    /api/job-descriptions
POST   /api/job-descriptions
GET    /api/job-descriptions/{id}
POST   /api/job-descriptions/{id}/analyze
```

### Matching

```txt
POST   /api/match-reports
GET    /api/match-reports/{id}
```

### AI Interview

```txt
POST   /api/ai/interviews
GET    /api/ai/interviews/{id}
POST   /api/ai/interviews/{id}/messages
POST   /api/ai/interviews/{id}/generate-patches
```

### CV Patches

```txt
GET    /api/cv-versions/{id}/patches
POST   /api/cv-patches/{id}/accept
POST   /api/cv-patches/{id}/reject
POST   /api/cv-patches/{id}/regenerate
```

### Templates

```txt
GET    /api/templates
GET    /api/templates/{id}
```

---

## 11. CV Data JSON Example

```json
{
  "personal_info": {
    "full_name": "Nguyen Anh Tu",
    "email": "example@gmail.com",
    "phone": "",
    "location": "Vietnam",
    "github": "",
    "linkedin": "",
    "portfolio": ""
  },
  "summary": "Final-year Software Engineering student interested in frontend and full-stack development.",
  "skills": {
    "frontend": ["React", "Vue", "TypeScript", "TailwindCSS"],
    "backend": ["Laravel", "NestJS", "PostgreSQL"],
    "tools": ["Git", "Docker", "Swagger"]
  },
  "projects": [
    {
      "id": "edura",
      "name": "Edura Tutoring Platform",
      "role": "Full-stack Developer",
      "tech_stack": ["React", "NestJS", "PostgreSQL"],
      "bullets": [
        "Built tutor booking and course browsing pages.",
        "Integrated backend APIs for authentication and booking flows."
      ]
    }
  ],
  "education": [
    {
      "school": "FPT University",
      "major": "Software Engineering",
      "period": "2022 - Present"
    }
  ]
}
```

---

## 12. CV Patch JSON Example

```json
{
  "section": "projects",
  "item_id": "edura",
  "field": "bullets",
  "operation": "replace",
  "old_value": [
    "Built tutor booking and course browsing pages."
  ],
  "new_value": [
    "Developed tutor detail, booking form, authentication, and payment success pages using React and TypeScript.",
    "Integrated REST APIs generated from Swagger with RTK Query to handle booking, authentication, and payment confirmation flows."
  ],
  "reason": "Improves evidence for React, TypeScript, REST API integration, and frontend implementation experience.",
  "evidence_sources": [
    "existing_cv",
    "user_follow_up_answer"
  ]
}
```

---

## 13. Security and Safety Rules

Because this project uses AI to edit user career documents, it should follow these rules:

1. AI must not invent experience, metrics, companies, awards, or skills.
2. AI must clearly distinguish between existing evidence and missing evidence.
3. AI-generated changes must be reviewed by the user before being applied.
4. Sensitive user data should not be logged unnecessarily.
5. API keys must be stored in environment variables.
6. Tool calls should be logged for debugging and auditing.
7. Prompt outputs should be validated with JSON Schema before saving.
8. CV patches should include reasons and evidence sources.

---

## 14. Local Development Setup

### Prerequisites

Install:

- Node.js 20+
- npm or pnpm
- PHP 8.3+
- Composer
- MySQL or PostgreSQL
- Redis optional
- Docker optional

### Clone Repository

```bash
git clone <your-repository-url>
cd careerfit-ai
```

### Install Frontend

```bash
cd apps/web
npm install
npm run dev
```

### Install Backend

```bash
cd apps/api
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

### Queue Worker Optional

```bash
php artisan queue:work
```

### Scheduler Optional

```bash
php artisan schedule:work
```

---

## 15. Environment Variables

Example backend `.env` values:

```env
APP_NAME="CareerFit AI"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=careerfit_ai
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=database
CACHE_STORE=database
SESSION_DRIVER=database

AI_PROVIDER=openai
AI_API_KEY=
AI_MODEL=gpt-4.1-mini
```

Frontend `.env` example:

```env
VITE_API_BASE_URL=http://localhost:8000/api
```

---

## 16. Deployment Target

The project is designed to be deployable on a VPS.

Recommended VPS stack:

```txt
Ubuntu
Nginx
PHP-FPM
MySQL/PostgreSQL
Redis
Supervisor
SSL with Let's Encrypt
Node.js for frontend build
```

Deployment responsibilities:

- Serve Vue static files through Nginx.
- Run Laravel API with PHP-FPM.
- Run Laravel queue worker with Supervisor.
- Configure cron for Laravel Scheduler.
- Store uploaded files securely.
- Configure HTTPS.
- Back up database periodically.

---

## 17. MVP Scope

The recommended MVP includes:

- User authentication
- Structured CV profile editor
- CV template preview
- Job Description input
- JD analysis
- CV-JD match report
- AI follow-up questions
- AI-generated structured CV patches
- Accept/reject patch flow
- CV versioning
- PDF export through browser print or simple HTML-to-PDF

Not included in MVP:

- Fully automatic PDF/DOCX parsing
- LinkedIn integration
- Auto-apply jobs
- Recruiter messaging
- Advanced ATS scoring
- Full multi-agent orchestration

---

## 18. Roadmap

### Phase 1: Foundation

- Set up monorepo
- Build auth
- Build CV profile CRUD
- Build template preview
- Build basic CV versioning

### Phase 2: JD and Matching

- Add Job Description input
- Add JD analyzer
- Implement skill extraction
- Implement rule-based matching score
- Generate match report

### Phase 3: AI Revision

- Add AI interview session
- Generate follow-up questions
- Generate structured CV patches
- Add patch accept/reject flow
- Add patch history

### Phase 4: Export and Polish

- Improve CV templates
- Add PDF export
- Add dashboard
- Improve UI/UX
- Add deployment scripts

### Phase 5: Advanced AI

- Add tool call logs
- Add Review Agent
- Add ATS Optimization Agent
- Add multi-agent orchestration
- Add better evidence validation

---

## 19. Project Positioning

CareerFit AI is not just a chatbot for CV writing. It is a structured CV optimization platform that uses AI as a controlled assistant.

Key differences:

- CV content is stored as structured JSON.
- AI generates patches instead of directly overwriting the CV.
- Users approve every change.
- The system asks follow-up questions before rewriting weak sections.
- Matching is based on both deterministic scoring and AI explanation.
- Each CV version is targeted to a specific Job Description.

---

## 20. License

This project is intended for educational and portfolio purposes. License can be updated later.

