# Error Standard

**ERROR-STD-001:** The common error envelope is defined in [contracts/common/http.md](../contracts/common/http.md).

**ERROR-STD-002:** Use a stable upper-snake-case code. Code ownership is global when more than one
epic can return it; a domain-specific code is owned by the narrowest shared
contract that needs it. Do not reuse a code for different remediation actions.

| Situation | HTTP status | Code |
| --- | --- | --- |
| Invalid request fields | 422 | `VALIDATION_FAILED` |
| Unauthenticated session | 401 | `UNAUTHENTICATED` |
| Authorized identity lacks access | 404 | `RESOURCE_NOT_FOUND` |
| State precondition is unmet | 409 | capability-specific code |
| Session or CSRF state expired | 419 | `SESSION_EXPIRED` |
| Rate limit exceeded | 429 | `RATE_LIMITED` |
| Unexpected server failure | 500 | `INTERNAL_ERROR` |

**ERROR-STD-003:** Messages must be safe to display. Internal diagnosis belongs in sanitized logs,
not in the API payload.
