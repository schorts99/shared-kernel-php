# schorts/shared-kernel

Shared kernel value objects and domain abstractions for building domain-driven PHP applications.

## 📦 Installation

Require the package via Composer:

```bash
composer require schorts/shared-kernel
```

Composer will autoload classes under the namespace:

```php
Schorts\SharedKernel\
```

For example, classes in `src/ValueObjects/` are available under:

```php
use Schorts\SharedKernel\ValueObjects\EmailValue;
```

## 🧩 Features

- **Value Objects**

  Immutable primitives with validation and equality logic:

  - `ArrayValue`
  - `BooleanValue`
  - `CoordinatesValue`
  - `DateValue`
  - `EmailValue`
  - `EnumValue`
  - `FloatValue`
  - `IntegerValue`
  - `ObjectValue`
  - `PhoneValue`
  - `SlugValue`
  - `StringValue`
  - `URLValue`
  - `UUIDValue`
- **Domain Events**
  - `DomainEvent` base class with metadata and primitives serialization.
  - `DomainEventMetadata` and `DomainEventPrimitives` DTOs.
- **Entities**
  - `Entity` base class with identity and domain event recording.
- **DAO Abstraction**
  - `DAO` contract for persistence operations with support for `UnitOfWork` and delete modes.
- **CQRS – Queries**
  - `Query` interface and `AbstractQuery` base class with metadata, correlation/causation IDs, headers and context.
  - `QueryMetadata` and `QueryPrimitives` DTOs.
  - Built-in error types:
    - `QueryNotRegistered`
    - `QueryAlreadyRegistered`
- **CQRS – Query Handlers**
  - `QueryHandler` interface (validate → authorize → execute pipeline).
  - `AbstractQueryHandler` with optional caching, logging and metrics support.
  - `QueryHandlerOptions` and `QueryHandlerContext` DTOs.
  - Pluggable `Cache` and `Logger` contracts.
  - Built-in error types:
    - `QueryAuthorizationError`
    - `QueryExecutionError`
    - `QueryValidationError`
- **CQRS – Query Bus**
  - `QueryBus` interface for registering handlers and dispatching queries.
  - `InMemoryQueryBus` implementation with middleware support.
  - `QueryBusMiddleware`, `QueryBusContext` and `QueryBusConfig`.
  - Bulk dispatch (`dispatchMany`) with `AggregateError` on partial failures.
- **i18n**
  - `TranslationResolver` contract for localised messages.


## 🚀 Usage

### Example: Creating a Value Object

```php
use Schorts\SharedKernel\ValueObjects\EmailValue;

class UserEmail extends EmailValue {
  public function getAttributeName(): string {
    return 'user_email';
  }
}

$email = new UserEmail('test@example.com');

if ($email->isValid()) {
  echo "Valid email: " . $email;
}

```

### Example: Defining a Domain Event

```php
use Schorts\SharedKernel\DomainEvent\DomainEvent;

class UserRegisteredEvent extends DomainEvent {
  public function getEventName(): string {
    return 'user.registered';
  }
}
```

### Example: Entity with Domain Events

```php
use Schorts\SharedKernel\Entity\Entity;
use Schorts\SharedKernel\ValueObjects\ValueObject;
use Schorts\SharedKernel\Model\Model;

class UserEntity extends Entity {
  public function toPrimitives(): Model {
    // return DTO representation
  }
}
```

### Example: Defining a Query

```php
use Schorts\SharedKernel\Query\AbstractQuery;

final class GetUserByIdQuery extends AbstractQuery
{
  public function __construct(
    public readonly string $userId,
    string $correlationId,
    ?array $customMetadata = null,
  ) {
    parent::__construct($correlationId, $customMetadata);
  }

  public function getType(): string
  {
    return 'user.get_by_id';
  }

  public function toPrimitives(): \Schorts\SharedKernel\Query\QueryPrimitives
  {
    $primitives = parent::toPrimitives();
    // You can enrich payload here if needed
    return $primitives;
  }
}
```

### Example: Implementing a Query Handler

```php
use Schorts\SharedKernel\Query\AbstractQueryHandler;
use Schorts\SharedKernel\Query\QueryHandlerContext;

/**
 * @extends AbstractQueryHandler<GetUserByIdQuery, array>
 */
final class GetUserByIdHandler extends AbstractQueryHandler
{
  public function execute(Query $query, QueryHandlerContext $context): mixed
  {
    /** @var GetUserByIdQuery $query */
    // Fetch user from repository / DAO …
    return [
      'id' => $query->userId,
      'name' => 'John Doe',
    ];
  }

  // Optional overrides:
  // public function validate(Query $query): void { … }
  // public function authorize(Query $query): void { … }
  // public function getCacheKey(Query $query): ?string { … }
}
```

### Example: Using the Query Bus

```php
use Schorts\SharedKernel\Query\InMemoryQueryBus;
use Schorts\SharedKernel\Query\QueryBusConfig;

$bus = new InMemoryQueryBus();

$bus->register('user.get_by_id', new GetUserByIdHandler(
  new \Schorts\SharedKernel\Query\QueryHandlerOptions(
    cache: true,
    cacheTtl: 300_000, // 5 minutes
    logging: true,
  )
));

// Optional middleware
$bus->use(new class implements \Schorts\SharedKernel\Query\QueryBusMiddleware {
  public function beforeDispatch(Query $query, \Schorts\SharedKernel\Query\QueryBusContext $context): void
  {
    // e.g. start timer, add tracing span
  }

  public function afterDispatch(Query $query, mixed $result, \Schorts\SharedKernel\Query\QueryBusContext $context): void
  {
    // e.g. record metrics
  }

  public function onError(Query $query, \Throwable $error, \Schorts\SharedKernel\Query\QueryBusContext $context): void
  {
    // e.g. log / alert
  }
});

$query = new GetUserByIdQuery(
  userId: 'user-123',
  correlationId: 'corr-abc-123',
);

$result = $bus->dispatch($query);
```

### Example: Bulk Dispatch

```php
$results = $bus->dispatchMany([
  new GetUserByIdQuery('user-1', 'corr-1'),
  new GetUserByIdQuery('user-2', 'corr-2'),
]);
```

## 📜 License 

LGPL-3.0-or-later
