# BMAD Project Documentation & Work Breakdown Guide

## 1. Mục đích

Tài liệu này quy định cách sử dụng BMAD trong project để tránh:

- Duplicate business rules và contracts giữa nhiều Story.
- Story quá lớn nhưng không được breakdown thành implementation tasks.
- Agent tự invent technical decisions khi code.
- `_bmad-output/` trở thành nơi chứa toàn bộ documentation lâu dài của project.
- Global standards, Epic-level rules và Story-level requirements bị trộn lẫn.

Mô hình chuẩn:

```text
Organization / Engineering Standards
        ↓
Project Global
        ↓
Epic
        ↓
Story
        ↓
Implementation Tasks
        ↓
Code
        ↓
Verification / Review
```

---

# 2. Quy tắc phân tầng

## 2.1 Global / Project-level

Một rule, contract hoặc standard nên đặt ở Global khi:

- Nhiều Epic cùng sử dụng.
- Là convention chung của toàn hệ thống.
- Là architecture/security/data/API/testing standard.
- Không thuộc riêng một business capability.

Ví dụ:

```text
Global
├── Architecture principles
├── API conventions
├── Error response standard
├── Security baseline
├── Data conventions
├── Validation conventions
├── Observability
├── Reliability patterns
├── Testing standards
└── Global NFRs
```

Ví dụ các rule Global:

```text
API-STD-001
All API timestamps use UTC ISO-8601.

ERROR-STD-001
All API errors use:
{
  code,
  message,
  details
}

SEC-STD-001
Sensitive cookies must use Secure in production.

TEST-STD-001
Critical user flows require integration or E2E coverage.
```

Không copy các rule này vào từng Epic hoặc Story.

Story/Task chỉ reference ID tương ứng.

---

## 2.2 Epic-level

Epic chứa những thứ dùng chung giữa nhiều Story trong cùng một business capability.

Ví dụ:

```text
Epic: Authentication
```

Có thể chứa:

```text
- Epic Goal
- Scope
- Shared Domain Concepts
- Shared Business Rules
- Shared Contracts
- Shared State / Lifecycle Model
- Shared Validation Rules
- Shared Security Requirements
- Shared Error Taxonomy
- Shared Integration Rules
- Shared UX Behavior
- Epic-specific NFRs
- Test Strategy
- Assumptions
- Out of Scope
- Epic Definition of Done
```

Ví dụ:

```text
AUTH-BR-001
Disabled account cannot authenticate.

AUTH-BR-002
Authentication errors must not reveal whether an account exists.

AUTH-CONTRACT-SESSION-001
Refresh sessions are server-side state with explicit expiry and revocation.

AUTH-STATE-001
ACTIVE → REVOKED
ACTIVE → EXPIRED
```

Nếu một rule chỉ dùng cho đúng một Story, không cần đẩy lên Epic.

---

## 2.3 Story-level

Story đại diện cho một user-visible capability hoặc independently valuable behavior.

Ví dụ:

```text
Story: Register an Account

As a visitor
I want to create an account
So that I can access the application.
```

Story nên chứa:

```text
- User behavior
- Story-specific business rules
- Acceptance Criteria
- Edge cases
- Referenced shared rules/contracts
- Story-specific constraints
```

Ví dụ:

```text
References:
- API-STD-001
- ERROR-STD-001
- AUTH-BR-001

Story-specific rule:
REG-BR-001
Email must be unique after canonical normalization.
```

---

# 3. Acceptance Criteria

Acceptance Criteria chủ yếu thuộc Story.

AC trả lời:

> Khi nào Story cụ thể này được coi là hoàn thành?

Ví dụ:

```text
AC-01
Given a valid email and password
When the visitor submits the registration form
Then the account is created successfully.

AC-02
Given an email that already exists
When registration is attempted
Then the request is rejected with EMAIL_ALREADY_EXISTS.

AC-03
Given invalid input
When registration is submitted
Then validation errors are returned using ERROR-STD-001.
```

Không dùng một bộ Story AC khổng lồ cho cả Epic.

Epic có thể có:

```text
Epic Definition of Done
Cross-story completion criteria
```

Ví dụ:

```text
- All authentication endpoints follow ERROR-STD-001.
- All session-creating flows follow AUTH-CONTRACT-SESSION-001.
- Security-critical authentication flows have integration tests.
```

---

# 4. Implementation Task Breakdown

Story không nên được code tự do ngay nếu còn nhiều technical surfaces.

Trước implementation, Story phải được breakdown thành concrete tasks.

Ví dụ:

```text
Story: Register an Account

T1 — Define registration behavior and contracts
T2 — Implement backend registration use case
T3 — Implement persistence
T4 — Implement registration API
T5 — Implement frontend UI
T6 — Implement frontend validation
T7 — Integrate frontend with API
T8 — Implement error/message handling
T9 — Add unit tests
T10 — Add integration/E2E tests
```

Task là technical step để hoàn thành Story.

Không biến các technical tasks thành Story nếu chúng không tạo user-visible value độc lập.

Sai:

```text
Story 1: Registration API
Story 2: Registration UI
Story 3: Registration Integration
Story 4: Registration Tests
```

Ưu tiên:

```text
Story: Register an Account
├── API task
├── UI task
├── Integration task
└── Test task
```

---

# 5. Nội dung của một Task

Mỗi implementation task nên có:

```text
Task ID
Goal
Scope
References
Dependencies
Affected modules/files
Technical decisions
Implementation notes
Done conditions
Verification
```

Ví dụ:

```text
T4 — Implement registration API

Goal:
Expose account registration through the public API.

References:
- API-STD-001
- ERROR-STD-001
- REG-CONTRACT-001
- REG-BR-001

Affected modules:
- Auth/Application
- Auth/Interface/API
- Validation

Implementation:
- Add POST /auth/register
- Validate request DTO
- Execute RegisterAccount use case
- Map domain errors to API errors
- Return response according to REG-CONTRACT-001

Done when:
- Valid request creates account
- Duplicate email returns defined conflict code
- Invalid request follows global error contract
- API documentation is updated
- Integration tests pass
```

---

# 6. Technical Decisions vs Contracts

Không nhét mọi technical detail vào một câu Task.

Phân biệt:

## Technical Decision

Technical Decision trả lời:

> Chúng ta chọn cách triển khai nào?

Ví dụ:

```text
- Redis is used as the auth session store.
- Refresh token is stored in an HttpOnly cookie.
- Access token uses JWT.
```

## Contract

Contract trả lời:

> Các component phải tương tác với nhau theo quy ước nào?

Ví dụ:

```text
AUTH-COOKIE-CONTRACT-001

Cookie:
name: refresh_token
HttpOnly: true
Secure: production
SameSite: Lax
Path: /auth
TTL: 7 days
```

Hoặc:

```text
POST /auth/login

Request:
{
  email,
  password
}

Response 200:
{
  accessToken,
  expiresIn,
  user
}

Side effect:
Set refresh_token cookie.

Response 401:
{
  code: "INVALID_CREDENTIALS",
  message: "Email or password is incorrect."
}
```

Task phải reference contract thay vì tự định nghĩa lại.

---

# 7. Khi nào nâng rule lên tầng cao hơn

Rule of thumb:

```text
1 Story dùng
→ Story

Nhiều Story trong cùng Epic dùng
→ Epic

Nhiều Epic dùng
→ Project Global

Nhiều Project dùng
→ Organization / Engineering Standard
```

Luôn đặt rule ở mức thấp nhất mà vẫn cover đúng toàn bộ consumer của nó.

---

# 8. Nơi lưu tài liệu

## 8.1 `_bmad-output/`

Dùng cho BMAD-generated planning và implementation artifacts.

Khuyến nghị:

```text
_bmad-output/
├── planning-artifacts/
│   ├── prd.md
│   ├── architecture.md
│   ├── epics/
│   │   ├── epic-authentication/
│   │   │   ├── README.md
│   │   │   ├── business-rules.md
│   │   │   ├── contracts.md
│   │   │   └── stories/
│   │   │       └── register-account/
│   │   │           ├── README.md
│   │   │           ├── requirements.md
│   │   │           ├── contract.md
│   │   │           ├── tasks.md
│   │   │           └── verification.md
│   │   └── epic-user-management/
│   └── ...
│
├── implementation-artifacts/
│   ├── sprint-status.yaml
│   └── sprints/
│       └── ...
│
└── project-context.md
```

`_bmad-output/` trả lời:

> Chúng ta đang planning/building cái gì?

---

## 8.2 `docs/`

Dùng cho stable source-of-truth documentation của project.

Khuyến nghị:

```text
docs/
├── architecture/
│   ├── overview.md
│   └── adr/
│
├── standards/
│   ├── api.md
│   ├── errors.md
│   ├── security.md
│   ├── data.md
│   ├── validation.md
│   ├── observability.md
│   ├── reliability.md
│   └── testing.md
│
├── contracts/
│   ├── common/
│   └── ...
│
└── domain/
    └── glossary.md
```

`docs/` trả lời:

> Hệ thống này fundamentally hoạt động theo rule và standard nào?

---

# 9. Epic-specific documents nên đặt ở đâu?

Nếu tài liệu chỉ phục vụ planning của một Epic, đặt toàn bộ package Epic và
các Story con trong cùng hierarchy:

```text
_bmad-output/planning-artifacts/epics/
```

Ví dụ:

```text
_bmad-output/planning-artifacts/epics/epic-authentication/
├── README.md
├── business-rules.md
├── contracts.md
└── stories/
    ├── register-account/
    │   ├── README.md
    │   ├── requirements.md
    │   ├── contract.md
    │   ├── tasks.md
    │   └── verification.md
    └── sign-in-and-out/
        └── ...
```

Nếu một contract trở thành stable source of truth và được implementation/test/runtime dựa vào lâu dài:

```text
docs/contracts/
```

Ví dụ:

```text
docs/contracts/auth/session.md
docs/contracts/auth/cookie.md
docs/contracts/auth/errors.md
```

Epic artifact chỉ reference:

```text
References:
- docs/contracts/auth/session.md
- docs/contracts/auth/cookie.md
```

Không duplicate toàn bộ nội dung.

---

# 10. `project-context.md`

`_bmad-output/project-context.md` nên ngắn và chứa những rule agent phải luôn nhớ khi code.

Ví dụ:

```text
Backend
- Follow Clean Architecture boundaries.
- Controllers contain no business logic.
- Infrastructure integrations use adapters.

API
- Follow docs/standards/api.md.
- Follow docs/standards/errors.md.

Security
- Follow docs/standards/security.md.

Testing
- New use cases require unit tests.
- External integrations require integration tests.
```

Không copy toàn bộ Architecture, PRD hoặc contracts vào `project-context.md`.

---

# 11. Workflow BMAD đề xuất

```text
PRD
 ↓
Architecture
 ↓
Global standards / contracts
 ↓
Create Epics & Stories
 ↓
Epic shared rules/contracts
 ↓
Story Acceptance Criteria
 ↓
Sprint Planning
 ↓
Select Story
 ↓
Break Story into implementation tasks
 ↓
Map tasks to contracts/rules/files
 ↓
Implement
 ↓
Unit / Integration / E2E
 ↓
Code Review
 ↓
Story Done
```

---

# 12. Prompt cho BMAD khi breakdown Story

Có thể dùng prompt sau trước khi implementation:

```text
Read the selected Story, its Acceptance Criteria, referenced Epic rules,
global standards, architecture decisions, and existing contracts.

Before coding:

1. Inspect the current repository and architecture.
2. Break the Story into concrete implementation tasks.
3. Cover all relevant technical surfaces, including where applicable:
   - domain/business logic
   - data/persistence
   - API/contracts
   - validation
   - security
   - UI/UX states
   - frontend/backend integration
   - error/message handling
   - caching/session/queue/external integrations
   - observability
   - unit tests
   - integration tests
   - E2E tests
4. Reference existing rules/contracts instead of duplicating them.
5. Identify missing contracts or technical decisions before implementation.
6. Map every Acceptance Criterion to one or more tasks.
7. Map tasks to affected modules/files.
8. Identify dependencies, risks, and ordering.
9. Do not expand scope beyond the Story.
10. Then implement tasks in dependency order and verify all Acceptance Criteria.
```

---

# 13. Definition of Ready cho Story

Story chỉ nên bắt đầu implementation khi:

```text
[ ] User behavior rõ ràng
[ ] Acceptance Criteria đủ testable
[ ] Shared Epic rules đã được reference
[ ] Global standards liên quan đã được reference
[ ] Required contracts đã tồn tại hoặc được xác định cần tạo
[ ] Technical decisions quan trọng đã được chốt
[ ] Dependencies rõ ràng
[ ] Story có thể breakdown thành concrete tasks
[ ] Scope không bao gồm nhiều user capability độc lập
```

Nếu Story chứa nhiều capability độc lập, hãy split thành nhiều Story trước.

---

# 14. Definition of Done cho Story

```text
[ ] Tất cả Acceptance Criteria pass
[ ] Tất cả implementation tasks hoàn thành
[ ] Không duplicate shared rules/contracts
[ ] API/contracts được cập nhật nếu thay đổi
[ ] Unit tests pass
[ ] Integration tests pass nếu applicable
[ ] E2E critical flow pass nếu applicable
[ ] Security requirements được verify
[ ] Error/message behavior đúng contract
[ ] Observability/audit được bổ sung nếu cần
[ ] Documentation/source-of-truth được cập nhật
[ ] Code review hoàn tất
```

---

# 15. Nguyên tắc cuối cùng

```text
Global
= rules shared across Epics

Epic
= rules shared across Stories

Story
= user-visible behavior + Acceptance Criteria

Task
= concrete technical work

Contract
= agreement between components

Architecture
= long-lived structural decisions

_bmad-output
= planning/build artifacts

docs/
= stable project source of truth
```

Không để agent tự invent một decision quan trọng chỉ vì Story chưa nói rõ.

Nếu một decision ảnh hưởng nhiều consumer, hãy nâng nó lên đúng scope và tạo source-of-truth trước khi tiếp tục implementation.
