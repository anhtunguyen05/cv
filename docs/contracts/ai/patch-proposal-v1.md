# Internal AI Patch Proposal Contract v1

**Status:** proposed implementation contract. It does not enable a real provider
or alter the current deterministic fake-provider baseline.

## Scope and authority

This is the private contract between Laravel and `careerfit-ai-service` for one
untrusted Patch candidate. It supports the proposed AI-PR-02 and AI-PR-03
increments in [the AI service blueprint](../../architecture/ai-service-implementation.md).

The executable v1 shapes are [request schema](schemas/patch-proposal-request-v1.schema.json)
and [response schema](schemas/patch-proposal-response-v1.schema.json). Their
schema limits use Unicode code points; Laravel/Python additionally enforce the
byte limits in this document before network transmission.

It is not a browser API and is not a replacement for the Laravel Patch API.
Laravel remains the owner of source selection, authorization, target resolution,
grounding, persistence, Patch decisions, and CV Version creation.

The server accepts this contract only after it has verified ownership and a
completed Evidence Interview. The AI service never obtains Laravel database
credentials, user sessions, CSRF tokens, or direct write access.

## Transport

```text
POST /internal/v1/patch-proposals
Content-Type: application/json
X-Correlation-ID: <opaque 32 lowercase hex characters>
Authorization: <deployment-approved short-lived Laravel service identity>
```

The endpoint is available only on the private service network. A private
network does not replace authentication. The production identity mechanism is
an explicit AI-PR-01 decision: it must be audience-bound to the AI service,
short-lived, rotatable/revocable, and replay-protected. Static shared tokens
are not an accepted production mechanism.

The service rejects a non-JSON request, an invalid/missing service identity,
body over the configured capability limit, or a body that fails the closed v1
schema. Internal failure details never reach the browser.

## Request: `PatchProposalRequestV1`

Every field is required unless marked optional. All objects are closed:
unknown fields are rejected rather than ignored.

```json
{
  "contract_version": "1.0",
  "execution_id": "01J00000000000000000000000",
  "request_hash": "0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef",
  "source": {
    "cv_version_id": "01J00000000000000000000001",
    "snapshot_hash": "abcdef0123456789abcdef0123456789abcdef0123456789abcdef0123456789"
  },
  "context": {
    "source_fragment": {
      "kind": "summary",
      "current_value": "Backend developer with Laravel experience."
    },
    "positive_evidence": [
      {
        "id": "01J00000000000000000000002",
        "area_signal_id": "docker",
        "answer": "Configured Docker Compose for a Laravel development environment."
      }
    ]
  },
  "constraints": {
    "target_allowlist": ["summary"],
    "locale": "en"
  }
}
```

### Identity and binding fields

| Field | Rule |
| --- | --- |
| `contract_version` | Exact literal `1.0`. A future breaking change uses a new endpoint or major version. |
| `execution_id` | Laravel-generated ULID for one logical provider invocation. It is opaque to the AI service. |
| `request_hash` | 64 lowercase SHA-256 hex characters. It is SHA-256 over UTF-8 RFC 8785 canonical JSON with `request_hash` omitted, prefixed by `careerfit:patch-proposal-request:v1\n`. The response must echo it exactly. |
| `source.cv_version_id` | Existing opaque Laravel CV Version ULID. It is correlation metadata, never authorization. |
| `source.snapshot_hash` | Exact immutable source snapshot hash in its existing stored 64 lowercase hex representation. A response bound to another source is rejected. |

### Minimum context

AI-PR-03 is **summary replacement only**. Laravel deterministically uses the
first eligible positive Evidence answer in the completed Interview. If a usable
summary or positive Evidence is unavailable, Laravel rejects before the network
call using its existing eligibility/error path; it does not choose an
Experience/Project target in this increment.

The proposed later extension is a discriminated, target-scoped union:

| `kind` | Required fields | Allowed use |
| --- | --- | --- |
| `summary` | `current_value` (nullable string, max 2,000 UTF-8 bytes) | Propose one `replace` for `summary`. |
| `experience_highlight_replace` | `item_id`, `highlight_index`, `current_value`, optional `local_context` | Propose one `replace` for that exact Experience highlight. |
| `experience_highlight_append` | `item_id`, optional `local_context` | Propose one `append` for that Experience item. |
| `project_highlight_replace` | `item_id`, `highlight_index`, `current_value`, optional `local_context` | Propose one `replace` for that exact Project highlight. |
| `project_highlight_append` | `item_id`, optional `local_context` | Propose one `append` for that Project item. |

`local_context`, where approved, is a short, field-classified contextual string
with a documented byte cap. It is not a serialized Experience, Project, CV,
Job Description, User object, or database row. Laravel may expose only one
fragment per request. The AI service must not infer another target from content.

`positive_evidence` contains one to five user-authored positive answers only.
Each `answer` is a normalized string of 1–2,000 UTF-8 bytes. `cannot_provide`,
missing, negative, foreign, or stale Evidence is rejected by Laravel before this
request is built.

`target_allowlist` is exactly `["summary"]` in AI-PR-03. Experience/project
variants require a future contract revision with target-specific fixtures and
Laravel mapper changes. `locale` is exactly `en` in this increment.

## Success response: `PatchProposalResponseV1`

```json
{
  "contract_version": "1.0",
  "execution_id": "01J00000000000000000000000",
  "request_hash": "0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef",
  "source": {
    "cv_version_id": "01J00000000000000000000001",
    "snapshot_hash": "abcdef0123456789abcdef0123456789abcdef0123456789abcdef0123456789"
  },
  "status": "succeeded",
  "candidate": {
    "target": {
      "section": "summary",
      "field": "summary",
      "item_id": null,
      "operation": "replace"
    },
    "proposed_text": "Backend developer with Laravel experience and hands-on Docker Compose configuration.",
    "reason": "Clarifies the Docker experience stated in the user's Evidence.",
    "evidence_source_ids": ["01J00000000000000000000002"]
  },
  "metadata": {
    "provider": "configured-provider",
    "model": "configured-model",
    "prompt_version": "patch-v1",
    "input_tokens": 123,
    "output_tokens": 45,
    "latency_ms": 678
  }
}
```

The AI service returns HTTP 200 only for a syntactically valid candidate. It
does not return a Laravel Patch ID, a decision, or a claim that trusted state
was saved.

`candidate.target` is an assertion, not authority. Laravel verifies that it
matches the one allowed source fragment and resolves its exact target, current
`old_value`, item ownership, and any collection hash from the locked snapshot.
The candidate must contain:

- exactly one `replace` or `append` operation allowed for that target;
- `proposed_text` of 1–2,000 UTF-8 bytes after Laravel's final normalization;
- `reason` of 1–500 UTF-8 bytes; and
- one to five distinct `evidence_source_ids`, each from the request's positive
  Evidence list.

The model must not return `old_value`, collection hashes, user IDs, Patch IDs,
CV Version IDs not supplied in `source`, or arbitrary JSON Patch operations.

Future highlight variants use Laravel's final highlight limit (currently 400
Unicode characters), not the summary byte limit, and require `highlight_index`
for a replacement.

### Metadata

Metadata is sanitized operational data. `provider`, `model`, and
`prompt_version` are allowlisted identifiers (1–120 ASCII characters). Token
counts and latency are non-negative bounded integers. Raw prompts, raw model
messages, secrets, headers, stack traces, and user content are forbidden from
metadata and normal logs.

## Laravel acceptance algorithm

Laravel must execute these steps in order:

1. Reserve the logical operation and store a server-generated `execution_id`,
   request hash, source IDs/hashes, correlation ID, and attempt metadata. For
   AI-PR-03, `logical_operation_key` is SHA-256 of
   `patch-generation:v1\n<interview-id>\n<predecessor-patch-id-or-empty>\n<predecessor-revision-or-empty>`.
2. Build the minimum-data DTO from the locked/approved context; validate it
   against the v1 schema before any network call.
3. Send the private authenticated request outside the database transaction.
   The AI service maintains a bounded TTL cache keyed by `(execution_id,
   request_hash)`: an identical retry returns the same response; a conflicting
   binding is rejected. This cache is non-authoritative and never creates a
   Laravel Patch.
4. Validate the response JSON/schema and exact-match its version,
   `execution_id`, `request_hash`, and source tuple to the reservation.
5. Use `PatchCandidateMapper` to derive the full existing Patch proposal from
   the locked snapshot. Then execute the existing authoritative Laravel target,
   source, Evidence, grounding, and safety validation.
6. In one database transaction, create at most one pending Patch for the stable
   logical operation, store the idempotent response, and terminalize the
   attempt/execution. AI-PR-03 adds a reservation record with unique
   `(user_id, logical_operation_key)`, immutable request/source binding columns,
   and sanitized retention metadata. A uniqueness conflict returns/replays the
   original result; it never creates a second pending Patch.

Any failure before step 6 records only sanitized attempt/execution metadata.
The source CV Version, Match Report, Evidence, and existing Patches are never
modified by generation.

## Internal errors

Internal responses use a compact safe envelope; Laravel maps them to its public
error contract and status codes.

```json
{
  "code": "AI_REQUEST_INVALID",
  "message": "The internal request could not be processed.",
  "correlation_id": "0123456789abcdef0123456789abcdef"
}
```

Allowed internal codes are `AI_REQUEST_INVALID`, `AI_UNAUTHENTICATED`,
`AI_UNAVAILABLE`, `AI_TIMEOUT`, `AI_RATE_LIMITED`, and `AI_CANDIDATE_INVALID`.
The envelope contains no model/provider response, token, endpoint detail, or
stack trace.

| HTTP status | Internal code | Required safe behavior |
| --- | --- | --- |
| 400, 415, 422 | `AI_REQUEST_INVALID` | Custom handler omits rejected values, header/body fragments, and framework exception text. |
| 401, 403 | `AI_UNAUTHENTICATED` | Do not disclose the identity validation reason. |
| 413 | `AI_REQUEST_INVALID` | Reject before JSON parsing; content encoding is identity only. |
| 429 | `AI_RATE_LIMITED` | Return only safe category/correlation data. |
| 502, 503 | `AI_UNAVAILABLE` | Do not expose upstream/deployment detail. |
| 504 | `AI_TIMEOUT` | No trusted state is implied. |

## Required fixtures

AI-PR-02 must add machine-readable fixtures alongside this document for at
least these cases:

1. valid summary replacement;
2. summary text at min/max boundary;
3. extra or nested unknown request/response field;
4. candidate target not matching the supplied fragment/allowlist;
5. duplicate, foreign, missing, negative, or unsupported Evidence reference;
6. mismatched execution/request/source binding;
7. invalid `contract_version`, ID/hash grammar, byte/count bounds, or metadata;
8. malformed JSON, unauthorized request, timeout, transport failure, and 429;
   and
9. prompt-injection text and sensitive-content canaries that must not appear in
   safe telemetry.

## Compatibility

The current deterministic fake provider remains the default while this contract
is introduced. Enabling `RemotePatchProposalProvider` is configuration-driven,
requires complete contract tests, and is still not authorization for real
provider traffic. Real model activation additionally requires the Epic 5
provider/privacy, audit, quality, rollout, and kill-switch approvals.
