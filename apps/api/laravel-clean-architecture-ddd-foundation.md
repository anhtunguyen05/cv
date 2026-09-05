# Laravel Clean Architecture + DDD Foundation Guide

> Mục tiêu: định hình một Laravel backend theo Clean Architecture kết hợp Domain-Driven Design (DDD), đủ chặt để scale nhưng không over-engineer ngay từ đầu.
>
> Nguyên tắc chính:
>
> - Domain không phụ thuộc Laravel.
> - Application điều phối use case, không chứa chi tiết framework/infrastructure.
> - Infrastructure implement các contract của Domain/Application.
> - Presentation xử lý HTTP/CLI và chuyển dữ liệu vào/ra Application.
> - Ưu tiên tổ chức theo **Business Module / Bounded Context**.
> - Chỉ tạo abstraction có khả năng được dùng thực sự; không tạo “base class cho mọi thứ” một cách máy móc.

---

# 1. Kiến trúc tổng thể

Project sử dụng **layer-first Clean Architecture**, trong đó Domain và Application được chia tiếp theo business area/module.

```text
app/
├── Domain/
│   ├── User/
│   │   ├── Entities/
│   │   ├── Aggregates/
│   │   ├── ValueObjects/
│   │   ├── Events/
│   │   ├── Enums/
│   │   ├── Repositories/
│   │   ├── Services/
│   │   ├── Specifications/
│   │   └── Exceptions/
│   │
│   ├── Resume/
│   │   ├── Entities/
│   │   ├── Aggregates/
│   │   ├── ValueObjects/
│   │   ├── Events/
│   │   ├── Enums/
│   │   ├── Repositories/
│   │   ├── Services/
│   │   ├── Specifications/
│   │   └── Exceptions/
│   │
│   └── Job/
│       ├── Entities/
│       ├── Aggregates/
│       ├── ValueObjects/
│       ├── Events/
│       ├── Enums/
│       ├── Repositories/
│       ├── Services/
│       ├── Specifications/
│       └── Exceptions/
│
├── Application/
│   ├── User/
│   │   ├── UseCases/
│   │   │   ├── Commands/
│   │   │   └── Queries/
│   │   ├── DTOs/
│   │   ├── Contracts/
│   │   ├── Mappers/
│   │   └── Exceptions/
│   │
│   ├── Resume/
│   │   ├── UseCases/
│   │   │   ├── Commands/
│   │   │   └── Queries/
│   │   ├── DTOs/
│   │   ├── Contracts/
│   │   ├── Mappers/
│   │   └── Exceptions/
│   │
│   └── Job/
│       ├── UseCases/
│       │   ├── Commands/
│       │   └── Queries/
│       ├── DTOs/
│       ├── Contracts/
│       ├── Mappers/
│       └── Exceptions/
│
├── Infrastructure/
│   ├── Persistence/
│   │   ├── Eloquent/
│   │   │   ├── Models/
│   │   │   ├── Repositories/
│   │   │   └── Mappers/
│   │   └── Query/
│   │
│   ├── External/
│   │   ├── OpenAI/
│   │   ├── Storage/
│   │   └── Mail/
│   │
│   ├── Cache/
│   │   └── Redis/
│   │
│   ├── Queue/
│   └── Providers/
│
├── Presentation/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Requests/
│   │   ├── Resources/
│   │   └── Middleware/
│   │
│   └── Console/
│       └── Commands/
│
└── Shared/
    ├── Domain/
    │   ├── Entities/
    │   ├── Events/
    │   ├── ValueObjects/
    │   ├── Exceptions/
    │   └── Contracts/
    │
    ├── Application/
    │   ├── Contracts/
    │   ├── DTOs/
    │   └── Results/
    │
    └── Infrastructure/
        ├── Persistence/
        ├── Clock/
        └── Providers/
```

Ý tưởng chính:

```text
Domain/User
Domain/Resume
Domain/Job
```

chứa business model tương ứng.

```text
Application/User
Application/Resume
Application/Job
```

chứa các use case điều phối business model tương ứng.

Trong khi đó:

```text
Infrastructure/
Presentation/
```

được tổ chức theo technical concern thay vì nhân bản toàn bộ technical layer cho từng module.

Ví dụ:

```text
Infrastructure/Persistence/Eloquent/Repositories/
├── EloquentUserRepository.php
├── EloquentResumeRepository.php
└── EloquentJobRepository.php
```

và:

```text
Presentation/Http/Controllers/
├── User/
├── Resume/
└── Job/
```

có thể chia subfolder theo business area khi số lượng file tăng.

Cấu trúc này phù hợp khi:

- project có số bounded context/module vừa phải;
- muốn nhìn rõ 4 layer Clean Architecture ở cấp root;
- muốn tránh việc mỗi module lặp lại `Domain/Application/Infrastructure/Presentation`;
- team Laravel quen với cách tổ chức theo layer hơn;
- vẫn muốn giữ Domain/Application được phân chia rõ theo business concept.

# 2. Dependency Rule

Dependency chỉ được đi theo hướng:

```text
Presentation
     |
     v
Application
     |
     v
Domain

Infrastructure
     |
     +----> Domain contracts
     |
     +----> Application contracts
```

Không được:

```text
Domain -> Laravel
Domain -> Eloquent
Domain -> Redis
Domain -> HTTP Client
Domain -> OpenAI SDK
Domain -> Queue implementation

Application -> Controller
Application -> Eloquent Model
Application -> Redis implementation
```

Cho phép:

```text
Infrastructure -> Domain
Infrastructure -> Application

Presentation -> Application
Presentation -> Domain type nếu cần map response
```

---

# 3. Shared Kernel

Các abstraction thực sự dùng chung nên đặt tại:

```text
app/Shared/
├── Domain/
│   ├── Entities/
│   │   ├── BaseEntity.php
│   │   └── AggregateRoot.php
│   ├── Events/
│   │   └── DomainEvent.php
│   ├── ValueObjects/
│   │   └── ValueObject.php
│   ├── Exceptions/
│   │   └── DomainException.php
│   └── Contracts/
│       └── Clock.php
│
├── Application/
│   ├── Bus/
│   │   ├── Command.php
│   │   ├── Query.php
│   │   └── CommandHandler.php
│   ├── DTOs/
│   │   └── Pagination.php
│   ├── Results/
│   │   └── PaginatedResult.php
│   └── Contracts/
│       └── TransactionManager.php
│
├── Infrastructure/
│   ├── Persistence/
│   ├── Clock/
│   └── Providers/
│
└── Presentation/
    └── Http/
        └── Responses/
```

Shared Kernel phải nhỏ.

Không nên biến `Shared/` thành nơi chứa mọi class “chưa biết đặt ở đâu”.

---

# 4. BaseEntity

File:

```text
app/Shared/Domain/Entities/BaseEntity.php
```

Ví dụ:

```php
<?php

declare(strict_types=1);

namespace App\Shared\Domain\Entities;

abstract class BaseEntity
{
    public function __construct(
        protected readonly string $id,
    ) {
    }

    public function id(): string
    {
        return $this->id;
    }

    final public function equals(self $other): bool
    {
        return static::class === $other::class
            && $this->id === $other->id;
    }
}
```

Vai trò:

- cung cấp identity chung;
- định nghĩa semantics `Entity = object có identity`;
- không chứa persistence logic;
- không extend `Illuminate\Database\Eloquent\Model`.

Không nên nhét các field như:

```text
created_at
updated_at
deleted_at
```

vào `BaseEntity` chỉ vì database có các field đó.

Nếu timestamps có ý nghĩa domain, model chúng rõ ràng.

---

# 5. AggregateRoot

File:

```text
app/Shared/Domain/Entities/AggregateRoot.php
```

Ví dụ:

```php
<?php

declare(strict_types=1);

namespace App\Shared\Domain\Entities;

use App\Shared\Domain\Events\DomainEvent;

abstract class AggregateRoot extends BaseEntity
{
    /** @var list<DomainEvent> */
    private array $domainEvents = [];

    final protected function recordDomainEvent(DomainEvent $event): void
    {
        $this->domainEvents[] = $event;
    }

    /**
     * @return list<DomainEvent>
     */
    final public function releaseDomainEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];

        return $events;
    }
}
```

Aggregate Root dùng để:

- bảo vệ invariant;
- là entry point duy nhất để thay đổi aggregate;
- record domain event;
- tránh mutate entity con từ ngoài aggregate một cách tùy ý.

Ví dụ:

```text
Resume
└── ResumeSection[]
```

Nếu `Resume` là aggregate root thì code ngoài aggregate nên thay đổi section thông qua:

```php
$resume->updateSection(...);
```

thay vì:

```php
$resume->sections()[0]->setContent(...);
```

---

# 6. DomainEvent

File:

```text
app/Shared/Domain/Events/DomainEvent.php
```

```php
<?php

declare(strict_types=1);

namespace App\Shared\Domain\Events;

use DateTimeImmutable;

abstract readonly class DomainEvent
{
    public function __construct(
        public string $eventId,
        public DateTimeImmutable $occurredAt,
    ) {
    }
}
```

Một event cụ thể:

```text
app/Domain/Resume/Domain/Events/ResumeCreated.php
```

```php
<?php

declare(strict_types=1);

namespace App\Modules\Resume\Domain\Events;

use App\Shared\Domain\Events\DomainEvent;
use DateTimeImmutable;

final readonly class ResumeCreated extends DomainEvent
{
    public function __construct(
        string $eventId,
        DateTimeImmutable $occurredAt,
        public string $resumeId,
        public string $userId,
    ) {
        parent::__construct($eventId, $occurredAt);
    }
}
```

Domain Event mô tả **fact đã xảy ra trong domain**.

Tên event nên dùng past tense:

```text
ResumeCreated
ResumeAnalysisRequested
ResumeRevisionApproved
JobDescriptionImported
```

Không nên:

```text
CreateResume
AnalyzeResume
```

vì đó là command/intention, không phải event.

---

# 7. ValueObject

File:

```text
app/Shared/Domain/ValueObjects/ValueObject.php
```

Có thể dùng marker abstraction đơn giản:

```php
<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObjects;

abstract readonly class ValueObject
{
}
```

Value Object cụ thể:

```php
<?php

declare(strict_types=1);

namespace App\Modules\User\Domain\ValueObjects;

use App\Shared\Domain\ValueObjects\ValueObject;
use InvalidArgumentException;

final readonly class Email extends ValueObject
{
    public string $value;

    public function __construct(string $value)
    {
        $normalized = strtolower(trim($value));

        if (! filter_var($normalized, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email address.');
        }

        $this->value = $normalized;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
```

Value Object nên:

- immutable;
- validate invariant trong constructor/factory;
- equality dựa trên value;
- không có identity riêng.

Ví dụ:

```text
Email
Money
ResumeTitle
JobDescription
AnalysisScore
Confidence
```

---

# 8. Domain Exception

File:

```text
app/Shared/Domain/Exceptions/DomainException.php
```

```php
<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exceptions;

use RuntimeException;

abstract class DomainException extends RuntimeException
{
}
```

Exception cụ thể:

```php
final class ResumeCannotBeEdited extends DomainException
{
}
```

Domain exception nên thể hiện business violation:

```text
ResumeAlreadySubmitted
RevisionAlreadyApproved
InvalidResumeStateTransition
AnalysisAlreadyRunning
```

Không dùng Domain Exception cho:

```text
database connection failed
Redis timeout
HTTP 500
OpenAI timeout
```

Các lỗi đó thuộc Infrastructure/Application boundary.

---

# 9. Repository Contract

Repository interface nên nằm trong Domain nếu nó quản lý aggregate.

Ví dụ:

```text
app/Domain/Resume/Domain/Repositories/ResumeRepository.php
```

```php
<?php

declare(strict_types=1);

namespace App\Modules\Resume\Domain\Repositories;

use App\Modules\Resume\Domain\Aggregates\Resume;

interface ResumeRepository
{
    public function findById(string $id): ?Resume;

    public function save(Resume $resume): void;

    public function delete(Resume $resume): void;
}
```

Repository không nên expose Eloquent:

```php
// Không nên
public function query(): Builder;
```

hoặc:

```php
// Không nên
public function findById(string $id): ResumeModel;
```

Nếu cần query phức tạp cho read side, tạo Application Query contract riêng.

---

# 10. Domain Service

Dùng Domain Service khi business rule:

- thực sự thuộc domain;
- nhưng không tự nhiên thuộc một Entity hoặc Value Object.

Ví dụ:

```text
ResumeMatchScoringService
RevisionEligibilityPolicy
```

Không nên gọi mọi service là Domain Service.

---

# 11. Specification / Policy

Có thể dùng nếu domain có rule phức tạp và tái sử dụng:

```text
Domain/
├── Specifications/
│   └── ResumeCanBeAnalyzed.php
```

Ví dụ:

```php
interface Specification
{
    public function isSatisfiedBy(object $candidate): bool;
}
```

Không nhất thiết tạo ngay khi project nhỏ.

---

# 12. Application Layer

Application chịu trách nhiệm:

- orchestration;
- authorization ở use-case level nếu phù hợp;
- transaction boundary;
- load aggregate;
- gọi domain behavior;
- persist;
- dispatch domain events;
- gọi external ports thông qua interface;
- trả DTO/result.

Application không nên chứa core business invariant.

---

# 13. Command / Query

Có thể dùng CQRS nhẹ.

## Command

```text
Application/UseCases/Commands/CreateResume/
├── CreateResumeCommand.php
└── CreateResumeHandler.php
```

```php
final readonly class CreateResumeCommand
{
    public function __construct(
        public string $userId,
        public string $title,
    ) {
    }
}
```

Handler:

```php
final class CreateResumeHandler
{
    public function __construct(
        private ResumeRepository $resumes,
        private TransactionManager $transactions,
    ) {
    }

    public function handle(CreateResumeCommand $command): string
    {
        return $this->transactions->run(function () use ($command): string {
            $resume = Resume::create(
                userId: $command->userId,
                title: $command->title,
            );

            $this->resumes->save($resume);

            return $resume->id();
        });
    }
}
```

## Query

```text
Application/UseCases/Queries/GetResume/
├── GetResumeQuery.php
├── GetResumeHandler.php
└── GetResumeResult.php
```

Read side không bắt buộc phải reconstruct aggregate nếu chỉ cần projection.

Có thể query trực tiếp qua Application contract:

```php
interface ResumeReadRepository
{
    public function findDetail(string $resumeId): ?ResumeDetailDTO;
}
```

Implementation vẫn nằm Infrastructure.

---

# 14. Application Contract / Port

Các external capability phải đi qua interface.

Ví dụ AI:

```text
app/Domain/Analysis/Application/Contracts/ResumeAnalyzer.php
```

```php
interface ResumeAnalyzer
{
    public function analyze(AnalysisInput $input): AnalysisResult;
}
```

Implementation:

```text
Infrastructure/External/OpenAI/OpenAIResumeAnalyzer.php
```

Application chỉ biết:

```text
ResumeAnalyzer
```

không biết:

```text
OpenAI
Gemini
Anthropic
HTTP Client
SDK
```

Tương tự:

```text
FileStorage
EmailSender
PdfRenderer
QueuePublisher
TransactionManager
Clock
IdGenerator
```

---

# 15. DTO

DTO dùng để truyền data qua boundary.

Không dùng Eloquent Model làm DTO.

Ví dụ:

```php
final readonly class ResumeDetailDTO
{
    public function __construct(
        public string $id,
        public string $title,
        public string $status,
    ) {
    }
}
```

---

# 16. Infrastructure Persistence

Ví dụ:

```text
Infrastructure/
└── Persistence/
    └── Eloquent/
        ├── Models/
        │   └── ResumeModel.php
        ├── Repositories/
        │   └── EloquentResumeRepository.php
        └── Mappers/
            └── ResumePersistenceMapper.php
```

Eloquent Model:

```php
final class ResumeModel extends Model
{
    protected $table = 'resumes';

    protected $fillable = [
        'id',
        'user_id',
        'title',
        'status',
    ];
}
```

Repository implementation:

```php
final class EloquentResumeRepository implements ResumeRepository
{
    public function __construct(
        private ResumePersistenceMapper $mapper,
    ) {
    }

    public function findById(string $id): ?Resume
    {
        $model = ResumeModel::query()->find($id);

        return $model
            ? $this->mapper->toDomain($model)
            : null;
    }

    public function save(Resume $resume): void
    {
        $this->mapper->persist($resume);
    }

    public function delete(Resume $resume): void
    {
        ResumeModel::query()
            ->whereKey($resume->id())
            ->delete();
    }
}
```

---

# 17. Persistence Mapper

Mapper giúp tránh Domain phụ thuộc ORM.

```php
final class ResumePersistenceMapper
{
    public function toDomain(ResumeModel $model): Resume
    {
        return Resume::reconstitute(
            id: $model->id,
            userId: $model->user_id,
            title: $model->title,
            status: ResumeStatus::from($model->status),
        );
    }

    public function persist(Resume $resume): void
    {
        ResumeModel::query()->updateOrCreate(
            ['id' => $resume->id()],
            [
                'user_id' => $resume->userId(),
                'title' => $resume->title()->value,
                'status' => $resume->status()->value,
            ],
        );
    }
}
```

DDD thường phân biệt:

```text
create()
```

với:

```text
reconstitute()
```

`create()`:

- tạo object mới;
- validate creation invariant;
- có thể record Domain Event.

`reconstitute()`:

- dựng lại aggregate từ persistence;
- không phát lại event lịch sử.

---

# 18. Infrastructure Provider

Binding Laravel nên nằm ở Infrastructure Provider.

```text
Infrastructure/Providers/ResumeServiceProvider.php
```

```php
final class ResumeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            ResumeRepository::class,
            EloquentResumeRepository::class,
        );
    }
}
```

Provider module có thể bind:

```text
Repository contracts
Application ports
External adapters
Read repositories
Transaction manager
```

---

# 19. Presentation Layer

HTTP:

```text
Presentation/
└── Http/
    ├── Controllers/
    ├── Requests/
    └── Resources/
```

Controller phải mỏng.

Ví dụ:

```php
final class CreateResumeController
{
    public function __invoke(
        CreateResumeRequest $request,
        CreateResumeHandler $handler,
    ): JsonResponse {
        $resumeId = $handler->handle(
            new CreateResumeCommand(
                userId: (string) $request->user()->id,
                title: $request->validated('title'),
            )
        );

        return response()->json(
            ['id' => $resumeId],
            201,
        );
    }
}
```

Controller chỉ nên:

```text
Request validation
      ↓
map input
      ↓
call Application
      ↓
map response
```

Không nên:

```text
Controller
→ Eloquent query
→ business validation
→ gọi OpenAI
→ update DB
→ dispatch queue
```

---

# 20. Form Request

Laravel `FormRequest` thuộc Presentation.

Nó chịu trách nhiệm validate **input format**, ví dụ:

```text
required
string
max
email
uuid
```

Business invariant vẫn ở Domain.

Ví dụ:

```text
"title required"
```

có thể ở Request.

Nhưng:

```text
"Resume đã APPROVED thì không được sửa"
```

phải nằm ở Domain.

---

# 21. API Resource

Laravel Resource chỉ chịu trách nhiệm serialize output.

```php
final class ResumeResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'status' => $this->status,
        ];
    }
}
```

---

# 22. TransactionManager

Application không nên gọi trực tiếp:

```php
DB::transaction(...)
```

nếu muốn giữ framework independence.

Contract:

```text
Shared/Application/Contracts/TransactionManager.php
```

```php
interface TransactionManager
{
    /**
     * @template T
     * @param callable(): T $callback
     * @return T
     */
    public function run(callable $callback): mixed;
}
```

Laravel implementation:

```php
final class LaravelTransactionManager implements TransactionManager
{
    public function run(callable $callback): mixed
    {
        return DB::transaction($callback);
    }
}
```

---

# 23. Clock

Không gọi `now()` trực tiếp khắp Domain nếu logic phụ thuộc thời gian.

Contract:

```php
interface Clock
{
    public function now(): DateTimeImmutable;
}
```

Implementation:

```php
final class SystemClock implements Clock
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }
}
```

Lợi ích:

- test deterministic;
- dễ fake thời gian.

---

# 24. ID Generator

Nếu dùng UUID/ULID:

```php
interface IdGenerator
{
    public function generate(): string;
}
```

Infrastructure:

```php
final class LaravelUlidGenerator implements IdGenerator
{
    public function generate(): string
    {
        return (string) Str::ulid();
    }
}
```

Không bắt buộc nếu ID generation đơn giản và không cần test isolation.

---

# 25. Domain Event Dispatching

Aggregate chỉ record event:

```php
$this->recordDomainEvent(
    new ResumeCreated(...)
);
```

Aggregate không gọi Laravel Event Dispatcher trực tiếp.

Application/Infrastructure lấy event sau khi save:

```php
$events = $resume->releaseDomainEvents();
```

sau đó dispatch.

Điều này giữ:

```text
Domain
```

không phụ thuộc:

```text
Illuminate\Events\Dispatcher
```

---

# 26. Integration Event

Phân biệt:

```text
Domain Event
vs
Integration Event
```

Domain Event:

```text
ResumeAnalysisCompleted
```

là event nội bộ domain.

Integration Event:

```text
resume.analysis.completed.v1
```

là contract được publish ra queue/message broker.

Không nên mặc định publish nguyên Domain Event ra RabbitMQ/Kafka.

Nên map:

```text
Domain Event
      ↓
Application/Infrastructure
      ↓
Integration Event
```

---

# 27. Outbox Pattern — khi cần

Khi cần publish event đáng tin cậy:

```text
Database transaction
├── save aggregate
└── save outbox event
        ↓
commit
        ↓
outbox worker
        ↓
message broker
```

Không cần implement Outbox ngay nếu application chưa có async integration quan trọng.

---

# 28. Laravel Default Files — giữ hay di chuyển?

Laravel sinh mặc định:

```text
app/
├── Http/
├── Models/
└── Providers/
```

Có thể migrate dần.

Khuyến nghị:

```text
app/Http/
```

có thể xóa dần sau khi chuyển controller/request sang module.

```text
app/Models/
```

có thể chuyển Eloquent model vào:

```text
Infrastructure/Persistence/Eloquent/Models/
```

`app/Providers/`

nên giữ provider ở đây hoặc có root provider load module provider.

Ví dụ:

```text
app/Providers/
└── ModuleServiceProvider.php
```

---

# 29. ModuleServiceProvider

Một root provider có thể đăng ký provider từng module:

```php
final class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(
            \App\Modules\Resume\Infrastructure\Providers\ResumeServiceProvider::class
        );

        $this->app->register(
            \App\Modules\Analysis\Infrastructure\Providers\AnalysisServiceProvider::class
        );
    }
}
```

Hoặc Laravel bootstrap có thể register trực tiếp provider tùy version.

---

# 30. Routes

Có hai hướng.

## Hướng A — route tập trung

```text
routes/api.php
```

```php
Route::prefix('resumes')->group(...);
```

Đơn giản và đúng Laravel convention.

## Hướng B — route theo module

```text
Domain/Resume/Presentation/Http/routes.php
```

Module provider load route.

Hợp khi project lớn.

Không cần làm hướng B ngay từ đầu nếu project còn nhỏ.

---

# 31. Database Migration

Migration vẫn nên giữ convention Laravel:

```text
database/migrations/
```

Không cần ép migration vào từng Domain module.

Lý do:

- migration là concern của deployment/database;
- Laravel tooling mặc định hoạt động tốt;
- giảm custom bootstrap.

Nếu project cực lớn mới cân nhắc module migration.

---

# 32. Factories / Seeders

Nên giữ:

```text
database/factories/
database/seeders/
```

Factory dùng Eloquent nên thuộc infrastructure/test concern, không phải Domain.

---

# 33. Testing Structure

```text
tests/
├── Unit/
│   ├── Domain/
│   └── Application/
│
├── Integration/
│   ├── Persistence/
│   └── External/
│
└── Feature/
    └── Http/
```

Hoặc theo module:

```text
tests/
└── Modules/
    └── Resume/
        ├── Domain/
        ├── Application/
        ├── Infrastructure/
        └── Presentation/
```

Khuyến nghị module-based khi codebase lớn.

---

# 34. Test Strategy

## Domain test

Không boot Laravel.

```text
pure PHP
fast
no DB
no HTTP
```

Test:

```text
Aggregate behavior
Value Object
Invariant
Domain Event
State transition
```

## Application test

Mock/fake contract:

```text
ResumeRepository
Clock
Analyzer
TransactionManager
```

## Infrastructure integration test

Dùng DB/test container nếu cần.

Test:

```text
Eloquent repository
Redis adapter
External API adapter
Mapper
```

## Feature test

Boot Laravel.

Test:

```text
HTTP
Auth
Validation
Serialization
End-to-end use case
```

---

# 35. PHPStan

Nên dùng PHPStan/Larastan.

Ví dụ:

```neon
parameters:
    level: 5

    paths:
        - app
        - tests

    tmpDir: var/phpstan

    treatPhpDocTypesAsCertain: true
```

Khi team dùng Windows/Linux/macOS, ưu tiên relative path:

```text
var/phpstan
```

và `.gitignore`:

```gitignore
/var/phpstan/
```

Không hard-code `/tmp/phpstan` nếu PHPStan chạy native trên nhiều OS.

---

# 36. Coding Rules

Mỗi PHP file:

```php
<?php

declare(strict_types=1);
```

Ưu tiên:

```text
final
readonly
constructor promotion
enum
typed properties
explicit return type
```

khi phù hợp.

Ví dụ:

```php
final readonly class CreateResumeCommand
{
    public function __construct(
        public string $userId,
        public string $title,
    ) {
    }
}
```

---

# 37. Naming Convention

## Entity

```text
Resume
User
Revision
Analysis
```

## Value Object

```text
Email
ResumeTitle
AnalysisScore
Confidence
```

## Command

```text
CreateResumeCommand
ApproveRevisionCommand
StartAnalysisCommand
```

## Command Handler

```text
CreateResumeHandler
ApproveRevisionHandler
StartAnalysisHandler
```

## Query

```text
GetResumeQuery
ListUserResumesQuery
```

## Domain Event

```text
ResumeCreated
RevisionApproved
AnalysisCompleted
```

## Repository

```text
ResumeRepository
UserRepository
```

Infrastructure:

```text
EloquentResumeRepository
EloquentUserRepository
```

## External Port

```text
ResumeAnalyzer
FileStorage
PdfRenderer
EmailSender
```

Adapter:

```text
OpenAIResumeAnalyzer
S3FileStorage
BrowsershotPdfRenderer
LaravelMailSender
```

---

# 38. Business Area Placeholder Structure

Ở giai đoạn setup foundation, **không tạo feature business thật**.

Chỉ định hình convention để sau này khi có feature mới, team biết file nên đi đâu.

Ví dụ conceptual:

```text
app/
├── Domain/
│   └── <BusinessArea>/
│       ├── Entities/
│       ├── Aggregates/
│       ├── ValueObjects/
│       ├── Events/
│       ├── Enums/
│       ├── Repositories/
│       ├── Services/
│       ├── Specifications/
│       └── Exceptions/
│
├── Application/
│   └── <BusinessArea>/
│       ├── UseCases/
│       │   ├── Commands/
│       │   └── Queries/
│       ├── DTOs/
│       ├── Contracts/
│       ├── Mappers/
│       └── Exceptions/
│
├── Infrastructure/
│   ├── Persistence/
│   │   └── Eloquent/
│   │       ├── Models/
│   │       ├── Repositories/
│   │       └── Mappers/
│   ├── External/
│   ├── Cache/
│   ├── Queue/
│   └── Providers/
│
└── Presentation/
    ├── Http/
    │   ├── Controllers/
    │   ├── Requests/
    │   ├── Resources/
    │   └── Middleware/
    └── Console/
        └── Commands/
```

`<BusinessArea>` chỉ là placeholder trong tài liệu.

Agent setup **không được tạo** các folder kiểu:

```text
Domain/Resume
Domain/User
Domain/Job
Application/Resume
...
```

nếu project chưa thực sự có requirement tương ứng.

Mục tiêu của phase này chỉ là tạo architectural foundation.


# 39. External Integration Placeholder

Ở phase setup, cũng không implement integration thật như OpenAI, Mail, Storage hay Redis adapter nếu project chưa dùng.

Chỉ giữ convention:

```text
Infrastructure/
├── External/
├── Cache/
├── Queue/
└── Persistence/
```

Khi có requirement thật sau này:

```text
Application/<Area>/Contracts/<Port>.php
```

sẽ định nghĩa abstraction, còn implementation nằm dưới:

```text
Infrastructure/External/
Infrastructure/Cache/
Infrastructure/Queue/
Infrastructure/Persistence/
```

Ví dụ tên trong tài liệu chỉ mang tính minh họa; setup agent không được tự tạo adapter business cụ thể.


# 40. Các file nên tạo ngay từ đầu

Nên tạo sớm:

```text
Shared/Domain/Entities/BaseEntity.php
Shared/Domain/Entities/AggregateRoot.php
Shared/Domain/Events/DomainEvent.php
Shared/Domain/ValueObjects/ValueObject.php
Shared/Domain/Exceptions/DomainException.php

Shared/Application/Contracts/TransactionManager.php

Shared/Infrastructure/Persistence/LaravelTransactionManager.php
```

Có thể tạo khi thực sự cần:

```text
Clock.php
IdGenerator.php
Specification.php
Command.php
Query.php
CommandBus.php
QueryBus.php
EventBus.php
Outbox
UnitOfWork
```

Không nên implement tất cả ngay.

---

# 41. Những thứ KHÔNG nên tạo chỉ để “đúng DDD”

Tránh:

```text
BaseRepository
BaseService
BaseController
BaseUseCase
BaseDTO
GenericRepository<T>
AbstractMapper
AbstractFactory
AbstractManager
```

nếu chúng chỉ tạo inheritance mà không cung cấp semantics thực sự.

Ví dụ:

```php
abstract class BaseService
{
}
```

không có giá trị.

Hoặc:

```php
interface BaseRepository
{
    public function findAll();
    public function findById();
    public function create();
    public function update();
    public function delete();
}
```

thường biến Repository thành CRUD abstraction thay vì domain abstraction.

Tốt hơn:

```php
interface ResumeRepository
{
    public function findById(ResumeId $id): ?Resume;

    public function save(Resume $resume): void;
}
```

---

# 42. Không ép mọi bảng thành Aggregate

Database table != Domain Aggregate.

Ví dụ:

```text
resumes
resume_sections
analysis_results
revision_requests
```

không có nghĩa phải có:

```text
4 aggregates
```

Aggregate được xác định dựa trên:

```text
transaction consistency boundary
business invariant
lifecycle
```

không phải schema.

---

# 43. Eloquent Model != Domain Entity

Đây là rule quan trọng nhất của structure này.

```text
Eloquent Model
```

là persistence model.

```text
Domain Entity
```

là business model.

Có thể ban đầu thấy duplicated:

```text
Resume
ResumeModel
```

nhưng hai object phục vụ hai concern khác nhau.

---

# 44. Recommended Development Flow

Khi bắt đầu implement feature trong tương lai, feature nên đi từ business vào infrastructure.

Ví dụ feature:

```text
Approve Resume Revision
```

Thứ tự:

```text
1. Define business behavior
        ↓
2. Domain model / invariant
        ↓
3. Domain unit tests
        ↓
4. Application command/use case
        ↓
5. Repository / external contracts
        ↓
6. Infrastructure implementation
        ↓
7. HTTP Controller + Request + Resource
        ↓
8. Integration / Feature tests
```

Không nên bắt đầu bằng:

```text
migration
→ Eloquent model
→ controller
→ rồi mới nghĩ business rule
```

với các feature có domain logic đáng kể.

CRUD đơn giản vẫn có thể đi nhanh hơn.

---

# 45. Definition of Done cho một Use Case

Một use case được coi là hoàn thiện khi:

- Input được validate ở đúng boundary.
- Business invariant nằm trong Domain.
- Application orchestration rõ ràng.
- Không có Eloquent trong Domain/Application.
- External dependency đi qua contract.
- Persistence implementation ở Infrastructure.
- Controller mỏng.
- Error mapping rõ ràng.
- Unit test cho business behavior.
- Integration test cho infrastructure quan trọng.
- Feature test cho API critical path.
- PHPStan pass.
- Laravel Pint pass.
- Test suite pass.

---

# 46. Dependency Checklist

Trước khi merge, check:

```text
[ ] Domain có import Illuminate\* không?
[ ] Domain có import Eloquent Model không?
[ ] Application có query Eloquent trực tiếp không?
[ ] Controller có business rule không?
[ ] External SDK có leak vào Application/Domain không?
[ ] Repository contract có return Eloquent Model không?
[ ] Aggregate invariant có thể bị bypass từ ngoài không?
[ ] Domain event có được record thay vì dispatch trực tiếp từ Domain không?
[ ] Framework-specific validation có bị dùng làm business invariant không?
```

Nếu có, xem lại boundary.

---

# 47. Structure cần có ngay sau phase setup

Sau phase setup, project chỉ cần đạt cấu trúc nền:

```text
app/
├── Domain/
├── Application/
├── Infrastructure/
│   ├── Persistence/
│   ├── External/
│   ├── Cache/
│   ├── Queue/
│   └── Providers/
├── Presentation/
│   ├── Http/
│   └── Console/
└── Shared/
    ├── Domain/
    │   ├── Entities/
    │   ├── Events/
    │   ├── ValueObjects/
    │   ├── Exceptions/
    │   └── Contracts/
    ├── Application/
    │   ├── Contracts/
    │   ├── DTOs/
    │   └── Results/
    └── Infrastructure/
        └── Persistence/
```

Không cần tạo các business area cụ thể ở phase này.

Chỉ khi bắt đầu feature thật mới thêm:

```text
Domain/<BusinessArea>/
Application/<BusinessArea>/
```

và các implementation tương ứng ở Infrastructure/Presentation.


# 48. Migration Strategy từ Laravel Skeleton — Setup Only

Sau khi:

```bash
composer create-project laravel/laravel .
```

phase hiện tại chỉ làm foundation.

## Bước 1

Tạo root architecture:

```text
app/
├── Domain/
├── Application/
├── Infrastructure/
├── Presentation/
└── Shared/
```

## Bước 2

Tạo các Shared abstractions thực sự cần thiết:

```text
Shared/Domain/Entities/BaseEntity.php
Shared/Domain/Entities/AggregateRoot.php
Shared/Domain/Events/DomainEvent.php
Shared/Domain/ValueObjects/ValueObject.php
Shared/Domain/Exceptions/DomainException.php
Shared/Application/Contracts/TransactionManager.php
Shared/Infrastructure/Persistence/LaravelTransactionManager.php
```

## Bước 3

Đăng ký binding framework cần thiết, ví dụ:

```text
TransactionManager
→ LaravelTransactionManager
```

## Bước 4

Thêm test cho foundation nếu có giá trị:

```text
BaseEntity equality
AggregateRoot event recording/releasing
```

## Bước 5

Chạy:

```text
PHPStan/Larastan
Pint
PHPUnit/Pest
```

## Không làm ở phase này

Không:

```text
- tạo User feature
- tạo Resume feature
- tạo Job feature
- tạo Analysis feature
- tạo Controller business
- tạo Eloquent business model
- tạo migration business
- tạo repository business
- tạo command/query business
- tạo external adapter thật
```

Không migrate existing feature trừ khi user yêu cầu riêng.

Mục tiêu duy nhất là tạo một architecture foundation sạch để feature sau này bám theo.


# 49. Agent Implementation Prompt — Setup Foundation Only

Copy prompt dưới đây cho coding agent.

```text
You are setting up the architectural FOUNDATION for an existing Laravel application using Clean Architecture combined with pragmatic Domain-Driven Design.

THIS IS A SETUP-ONLY TASK.

Do NOT implement any real business feature.
Do NOT create Resume, User, Job, Analysis, Revision, Auth, Template, Export, or any other business-specific use case unless such code already exists and must only be preserved.

Do NOT create sample business entities, controllers, repositories, migrations, commands, queries, DTOs, or external adapters.

ARCHITECTURE STYLE

This project uses LAYER-FIRST organization.

Required root structure:

app/
├── Domain/
├── Application/
├── Infrastructure/
├── Presentation/
└── Shared/

Business areas will be added later when real features are implemented.

TARGET FOUNDATION STRUCTURE

app/
├── Domain/
├── Application/
├── Infrastructure/
│   ├── Persistence/
│   ├── External/
│   ├── Cache/
│   ├── Queue/
│   └── Providers/
├── Presentation/
│   ├── Http/
│   └── Console/
└── Shared/
    ├── Domain/
    │   ├── Entities/
    │   ├── Events/
    │   ├── ValueObjects/
    │   ├── Exceptions/
    │   └── Contracts/
    ├── Application/
    │   ├── Contracts/
    │   ├── DTOs/
    │   └── Results/
    └── Infrastructure/
        └── Persistence/

Do not create empty business-area folders such as:

Domain/User
Domain/Resume
Application/User
Application/Resume

unless they already exist in the repository.

FOUNDATIONAL FILES TO CREATE

Create these only if equivalent abstractions do not already exist:

1. app/Shared/Domain/Entities/BaseEntity.php
   - abstract
   - framework-independent
   - identity-based equality
   - no Eloquent
   - no Laravel dependency

2. app/Shared/Domain/Entities/AggregateRoot.php
   - extends BaseEntity
   - stores DomainEvent objects internally
   - protected recordDomainEvent()
   - public releaseDomainEvents()
   - releasing clears the internal event list
   - must not dispatch Laravel events directly

3. app/Shared/Domain/Events/DomainEvent.php
   - framework-independent
   - eventId
   - occurredAt
   - immutable/readonly where compatible with project PHP version

4. app/Shared/Domain/ValueObjects/ValueObject.php
   - lightweight marker/base abstraction only
   - do not add reflection-heavy generic behavior

5. app/Shared/Domain/Exceptions/DomainException.php
   - base exception for domain/business-rule violations

6. app/Shared/Application/Contracts/TransactionManager.php
   - framework-independent
   - generic callable transaction boundary

7. app/Shared/Infrastructure/Persistence/LaravelTransactionManager.php
   - implements TransactionManager
   - delegates to Laravel DB::transaction()

OPTIONAL ABSTRACTIONS

Do NOT create these unless the CURRENT repository already demonstrates a concrete need:

- Clock
- IdGenerator
- Specification
- EventBus
- CommandBus
- QueryBus
- Outbox
- UnitOfWork
- Pagination abstraction
- Result abstraction

Do not build speculative architecture.

DEPENDENCY RULES

Domain:
- MUST NOT import Illuminate.*
- MUST NOT use Eloquent
- MUST NOT use Redis
- MUST NOT call external APIs
- MUST NOT depend on OpenAI/Gemini/Anthropic SDKs
- MUST NOT dispatch framework events

Application:
- may depend on Domain
- must remain framework-independent where practical
- must not query Eloquent directly
- external capabilities should later be expressed as contracts

Infrastructure:
- may depend on Domain/Application
- contains Laravel/Eloquent/Redis/queue/external SDK implementations

Presentation:
- contains HTTP/CLI concerns
- will call Application when real features are added later
- must not own business rules

IMPORTANT

This task does NOT include implementing a vertical slice.

Do NOT:
- create a sample Resume feature
- create a sample User feature
- create CRUD
- create controllers for demonstration
- create Eloquent business models for demonstration
- create migrations for demonstration
- create repository implementations for a fake feature
- create AI/OpenAI adapters for demonstration
- seed example business data
- move existing business code unless required to keep the application working

LARAVEL CONVENTIONS

Preserve:
- database/migrations
- database/factories
- database/seeders
- config
- bootstrap
- routes

Do not relocate existing application code unless the user explicitly asked for a migration/refactor.

SERVICE PROVIDER

Register only bindings needed by the foundation.

Example:
TransactionManager -> LaravelTransactionManager

Use the project's Laravel version-appropriate provider registration mechanism.

Do not create repository or external-service bindings without real implementations.

CODE STYLE

- declare(strict_types=1) in newly created PHP files
- explicit return types
- typed properties
- final where inheritance is not intended
- readonly where compatible and meaningful
- follow project formatting conventions

AVOID THESE ABSTRACTIONS

Do not create:

BaseService
BaseController
BaseUseCase
BaseDTO
GenericRepository
BaseRepository CRUD contract
AbstractManager
AbstractFactory

unless existing project semantics prove they are necessary.

TESTS

Create only meaningful foundation tests.

Recommended:

BaseEntity:
- same concrete type + same id => equal
- different id => not equal
- different concrete entity type => not equal

AggregateRoot:
- records domain events
- releaseDomainEvents() returns recorded events
- releaseDomainEvents() clears events

To test abstract classes, create test-only concrete fixtures under tests; do not create fake business entities in app/.

TransactionManager:
- only add an integration test if project testing infrastructure already supports database transactions cleanly

Do not test empty marker abstractions merely for coverage.

STATIC ANALYSIS

If PHPStan/Larastan exists:
- run it
- do not lower level
- do not add ignores just to pass new code
- keep tmpDir portable for Windows/Linux/macOS unless analysis always runs inside Linux containers

PROCESS

1. Inspect repository first.
2. Detect:
   - Laravel version
   - PHP version
   - composer dependencies
   - existing architecture
   - service providers
   - PHPStan/Larastan
   - Pint
   - PHPUnit/Pest
3. Check for equivalent existing abstractions before adding new ones.
4. Create only the root directories and Shared foundation required.
5. Add the TransactionManager Laravel implementation and binding if appropriate.
6. Add meaningful foundation tests.
7. Run:
   - composer validate if applicable
   - PHPStan/Larastan if configured
   - Pint/formatter
   - relevant tests
8. Fix only regressions introduced by this setup.
9. Do not start implementing business features.

EXPECTED FINAL REPORT

Report:

1. directories created
2. files created
3. files modified
4. service-container bindings added
5. tests/checks executed and results
6. optional abstractions intentionally NOT created
7. any conflicts with existing project structure
8. recommendations for how future features should use this foundation

SUCCESS CONDITION

The repository has a clean architectural skeleton and reusable DDD foundation, remains executable, passes applicable checks, and contains NO newly invented business feature.
```


# 50. Prompt Setup Ngắn Gọn

Nếu chỉ muốn agent setup nhanh foundation:

```text
Set up the Laravel Clean Architecture + DDD foundation only.

Use layer-first structure:

app/
├── Domain/
├── Application/
├── Infrastructure/
├── Presentation/
└── Shared/

Do NOT implement any real business feature.

Create only:
- Shared/Domain/Entities/BaseEntity.php
- Shared/Domain/Entities/AggregateRoot.php
- Shared/Domain/Events/DomainEvent.php
- Shared/Domain/ValueObjects/ValueObject.php
- Shared/Domain/Exceptions/DomainException.php
- Shared/Application/Contracts/TransactionManager.php
- Shared/Infrastructure/Persistence/LaravelTransactionManager.php
- required service-provider binding
- meaningful foundation tests

Do not create:
- User/Resume/Job/etc. sample features
- controllers
- business migrations
- Eloquent business models
- business repositories
- commands/queries
- external adapters
- generic BaseService/BaseRepository/BaseController abstractions

Inspect the repository before modifying it.
Preserve Laravel conventions and existing business code.
Run configured PHPStan/Larastan, Pint, and tests.
Report created/modified files and checks.
```

Sau khi foundation này ổn định, feature-specific prompt nên được viết riêng ở phase implement feature sau.


# 51. Architecture Principle Summary

Giữ 5 rule sau làm “north star”:

```text
1. Business rules belong to Domain.
2. Use-case orchestration belongs to Application.
3. Framework/database/external APIs belong to Infrastructure.
4. HTTP/CLI belongs to Presentation.
5. Dependencies always point inward.
```

Và rule thực tế quan trọng nhất:

```text
Don't optimize for folder purity.
Optimize for explicit business boundaries and replaceable infrastructure.
```

Một Clean Architecture tốt không phải codebase có nhiều folder nhất.

Nó là codebase mà khi đổi:

```text
MySQL -> PostgreSQL
OpenAI -> Gemini
REST -> CLI
Redis -> another cache
```

core business behavior ít bị ảnh hưởng nhất có thể.
