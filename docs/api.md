# API

## 1. Status

The repository currently exposes only one meaningful Laravel API endpoint:

- `GET /api/health`

Everything else in the README is still target design rather than implemented business API.

## 2. Current Endpoints

### Health Check

`GET /api/health`

Response shape:

```json
{
  "status": "ok",
  "service": "CareerFit AI",
  "timestamp": "2026-08-29T00:00:00Z"
}
```

### Web Root

`GET /`

Returns the default Laravel welcome page.

## 3. API Design Rules

- Use JSON for all application resources
- Keep routes versionable from day one
- Never allow the LLM to bypass server-side validation
- Treat patch application as a trusted API action, not a prompt side effect
- Prefer explicit resource IDs over implicit state

## 4. Proposed Domain Surface

The README implies the following resource groups:

- auth
- CV profiles
- CV versions
- job descriptions
- match reports
- interview sessions
- interview messages
- CV patches
- templates
- export jobs

## 5. Suggested Contract Shape

### CV Profile

```json
{
  "id": 1,
  "title": "Frontend Intern CV",
  "base_data": {},
  "created_at": "2026-08-29T00:00:00Z"
}
```

### Job Description Analysis

```json
{
  "id": 10,
  "job_title": "React Intern",
  "company_name": "Example Co",
  "required_skills": ["React", "TypeScript", "REST API"]
}
```

### Patch Proposal

```json
{
  "id": 42,
  "status": "pending",
  "section": "projects",
  "reason": "Adds stronger evidence for React and TypeScript."
}
```

## 6. Error Shape

Keep failures consistent:

```json
{
  "message": "Validation failed",
  "errors": {
    "field": ["The field is required."]
  }
}
```

## 7. Important Boundary

AI-generated suggestions should be submitted as proposals. Only the backend should accept, reject, or apply them.

## 8. Export Boundary

The MVP Export path is browser print/HTML from a Preview rendered from a saved
CV Version. Worker/server-side Export is a later capability and is not an MVP
dependency.
