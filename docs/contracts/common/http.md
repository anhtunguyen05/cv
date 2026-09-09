# Common HTTP Contract

## Scope

**HTTP-CONTRACT-000:** All product endpoints use `/api/v1`. `GET /api/health` is an operational
endpoint and intentionally does not use this contract.

## Success

**HTTP-CONTRACT-003:** One resource or action result:

```json
{ "data": {} }
```

**HTTP-CONTRACT-004:** Paginated collection:

```json
{
  "data": [],
  "meta": { "page": 1, "per_page": 20, "total": 0 },
  "links": { "self": "/api/v1/resources?page=1" }
}
```

**HTTP-CONTRACT-005:** Fields are `snake_case`; timestamps are UTC ISO-8601 strings; public product
IDs are ULID strings.

## Failure

```json
{
  "code": "VALIDATION_FAILED",
  "message": "One or more fields are invalid.",
  "details": {
    "title": [{ "code": "REQUIRED", "message": "Title is required." }]
  }
}
```

**HTTP-CONTRACT-006:** `code` is stable and machine-readable. `message` is safe for display. `details`
is structured and optional except for field validation. Never expose a raw
exception, stack trace, SQL error, credential, or another User's resource
existence.

## Boundary and framework failures

**HTTP-CONTRACT-001:** Every `/api/v1` request is rendered as JSON, including
router, middleware, validation, authentication, authorization, throttling, and
exception failures. Laravel's default HTML error pages are not product API
responses.

| Condition | Status | Code |
| --- | --- | --- |
| Malformed or unsupported request body | 400 | `INVALID_REQUEST_BODY` |
| Unauthenticated | 401 | `UNAUTHENTICATED` |
| Expired/invalid CSRF or session state | 419 | `SESSION_EXPIRED` |
| Hidden or absent protected resource | 404 | `RESOURCE_NOT_FOUND` |
| Route or method unavailable | 404 / 405 | `ROUTE_NOT_FOUND` / `METHOD_NOT_ALLOWED` |
| Rate limit exceeded | 429 | `RATE_LIMITED` |
| Unexpected failure | 500 | `INTERNAL_ERROR` |

**HTTP-CONTRACT-002:** API pagination uses page-number parameters `page` and
`per_page`; a capability documents its default and maximum `per_page`. A
response supplies `meta.page`, `meta.per_page`, `meta.total`, and `links.self`,
`links.first`, `links.last`, plus nullable `links.previous` and `links.next`.
Do not introduce cursor pagination for one endpoint without an explicit contract
revision.
