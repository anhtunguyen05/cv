# API Standard

References: [HTTP contract](../contracts/common/http.md), AD-2, AD-13, AD-14.

- **API-STD-001:** Product routes live under `/api/v1`; add a new version only for a deliberate
  breaking public-contract change.
- **API-STD-002:** Controllers translate HTTP requests to application inputs and application
  outputs to API resources. They do not contain business state transitions.
- **API-STD-003:** Use Laravel Form Requests at the boundary and API Resources for response
  serialization. Eloquent models are not API DTOs.
- **API-STD-004:** A protected resource lookup must authorize ownership before it is serialized.
  Non-owners receive the contract's non-disclosing failure behavior.
- **API-STD-005:** `GET /api/health` remains a small operational endpoint; do not use it as a
  product capability or move it under `/api/v1`.
- **API-STD-006:** Define a request/response contract before implementation and reference it from
  the owning epic/story. Domain-specific payload schemas belong in that epic's
  stable contract when they are reused beyond one story.
- **API-STD-007:** Configure API routes, middleware, and exception rendering so
  every `/api/v1` failure follows `HTTP-CONTRACT-001`, including errors raised
  before a controller is reached.
