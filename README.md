# schorts/shared-kernel

Shared kernel value objects and domain abstractions for building domain-driven PHP applications.

A modular, type-safe foundation for building expressive, maintainable applications. This package provides core abstractions for domain modeling, CQRS, event-driven architectures, access control, state management, and persistence — framework-agnostic and extensible.

## 📦 Installation

Require the package via Composer:

```bash
composer require schorts/shared-kernel
```

Composer will autoload classes under the namespace:

```php
Schorts\SharedKernel\
```

For example:

```php
use Schorts\SharedKernel\ValueObjects\EmailValue;
use Schorts\SharedKernel\CQRS\CommandBus\InMemoryCommandBus;
use Schorts\SharedKernel\EventBus\InMemoryEventBus;
```

## 🧩 Architecture & Features

### 🧬 Core Domain & DDD
- **Value Objects:** Immutable primitives with validation and equality logic:
  - `StringValue`, `IntegerValue`, `FloatValue`, `BooleanValue`, `DateValue`
  - `EmailValue`, `URLValue`, `PhoneValue`, `SlugValue`, `UUIDValue`
  - `HtmlValue`, `MarkdownValue`, `PathValue`
  - `ArrayValue`, `ObjectValue`, `EnumValue`, `CoordinatesValue`
  - `ValueNotValid` & `ValuesNotValid` domain exceptions.
  - Implements `\JsonSerializable` and `\Stringable`.
- **Entities:**
  - `Entity`: Identity-based entity with domain event recording and `pullDomainEvents`.
  - `EntityRegistry`: Registry for hydrating entities polymorphically from primitives or factories.
- **Aggregate Roots:**
  - `AggregateRoot`: Extends `Entity` with versioning, uncommitted-change tracking, domain-event sequencing, snapshots, and `fromPrimitives` / `fromSnapshot` factory methods.
- **Result Type:**
  - `Result`: Encapsulate success and failure outcomes without throwing. Includes `fromCallable`, `map`, `flatMap`, `mapError`, `combine`, `toPrimitives`, and `fromPrimitives`.

### ⚙️ Command & Query Processing (CQRS)
- **Commands:**
  - `Command` interface and `AbstractCommand` base class with metadata, causation/correlation IDs, headers, and context.
  - `CommandRegistry` for polymorphic hydration from primitives.
  - Built-in error types: `CommandNotRegistered`, `CommandAlreadyRegistered`, `CommandHandlerNotRegistered`.
- **Command Handlers:**
  - `CommandHandler` interface with pipeline: `validate` → `authorize` → `execute`.
  - `AbstractCommandHandler` with automatic retries, idempotency support, logging, and metrics.
  - Built-in error types: `CommandValidationError`, `CommandAuthorizationError`, `CommandExecutionError`, `CommandMaxRetriesExceeded`.
- **Command Bus:**
  - `CommandBus` interface and `InMemoryCommandBus` dispatcher.
  - Full middleware pipeline (`beforeDispatch`, `afterDispatch`, `onError`).
  - Unit of Work transactional integration and auto-event publishing.
- **Queries & Query Bus:**
  - `Query` interface and `AbstractQuery` base class.
  - `QueryHandler` and `AbstractQueryHandler` with caching, logging, and metrics.
  - `InMemoryQueryBus` with middleware and bulk dispatch (`dispatchMany`).

### 📡 Event Bus & Messaging
- **Domain Events:**
  - `DomainEvent` base class with metadata, correlation/causation tracking, and primitives serialization.
  - `DomainEventRegistry` for hydrating events from serialized payloads.
- **Event Bus:**
  - `EventBus` contract and `InMemoryEventBus` implementation.
  - Priority-based subscriber ordering.
  - Automatic retries on subscriber failure.
  - `DeadLetterStore` and `InMemoryDeadLetterStore` for capturing failed deliveries.
  - `EventStore` and `InMemoryEventStore` for auditing and event replays.
  - `EventSubscriber` and `AbstractEventSubscriber`.

### 🛡️ Access Control (RBAC & ABAC)
- **RBAC (Role-Based Access Control):**
  - `Permission`: Resource-action mapping (e.g. `'document'`, `'write'`) supporting wildcards (`'*'`) and manage actions.
  - `RBACPolicy`: Base class for defining role-based permissions and evaluating `can($roles, $action, $resource)`.
- **ABAC (Attribute-Based Access Control):**
  - `Predicate`: Composable attribute predicates to evaluate dynamic context (e.g. ownership, tenant limits).
  - Evaluated via `canWithAttributes` and `canAnyWithAttributes`.

### 💾 Persistence & Unit of Work
- **DAO (Data Access Object):**
  - `DAO` interface defining CRUD operations with Criteria filtering and soft/hard delete modes.
  - `InMemoryDAO`: Full in-memory implementation supporting Criteria filtering, sorting, pagination, and soft deletion.
- **Unit of Work:**
  - `UnitOfWork` and `InMemoryUnitOfWork` for transactional coordination with commit/rollback hooks.
  - `TransactionalUnitOfWorkRunner` for automatic transaction management.
- **Criteria:**
  - Fluent query builder with methods: `whereEqual`, `whereNotEqual`, `whereIn`, `whereNotIn`, `whereGreaterThan`, `whereLessThan`, `whereLike`, `whereBetween`, `orderBy`, `limitResults`, and `offsetResults`.

### 💻 Infrastructure & Utilities
- **Cache:** `Cache` interface and `InMemoryCache` with TTL and tag-based invalidation.
- **Idempotency:** `IdempotencyStore` and `InMemoryIdempotencyStore`.
- **Logger:** `Logger`, `ConsoleLogger`, `InMemoryLogger`, `NullLogger`, `CompositeLogger`, and `ScopedLogger`.
- **i18n:** `TranslationResolver` contract for localized domain messages.

---

## 🚀 Usage Examples

### 1. Value Objects

```php
use Schorts\SharedKernel\ValueObjects\EmailValue;
use Schorts\SharedKernel\ValueObjects\Exceptions\ValueNotValid;

class UserEmail extends EmailValue {
  public function getAttributeName(): string {
    return 'user_email';
  }
}

$email = new UserEmail('user@example.com');

if (!$email->isValid()) {
  throw new ValueNotValid($email->getAttributeName());
}

echo (string) $email; // user@example.com
```

### 2. CQRS Commands & Command Bus

```php
use Schorts\SharedKernel\CQRS\Command\AbstractCommand;
use Schorts\SharedKernel\CQRS\CommandHandler\AbstractCommandHandler;
use Schorts\SharedKernel\CQRS\CommandHandler\CommandHandlerContext;
use Schorts\SharedKernel\CQRS\CommandBus\InMemoryCommandBus;

final class RegisterUserCommand extends AbstractCommand
{
  public function getType(): string
  {
    return 'user.register';
  }
}

final class RegisterUserHandler extends AbstractCommandHandler
{
  protected function execute(Command $command, CommandHandlerContext $context): mixed
  {
    $payload = $command->getPayload();
    // Execute domain logic ...
    return ['userId' => 'usr_123'];
  }
}

$bus = new InMemoryCommandBus();
$bus->register('user.register', new RegisterUserHandler());

$command = new RegisterUserCommand('correlation-id-1', ['email' => 'alice@example.com']);
$result = $bus->dispatch($command);
```

### 3. Domain Events & Event Bus

```php
use Schorts\SharedKernel\DomainEvent\DomainEvent;
use Schorts\SharedKernel\EventBus\AbstractEventSubscriber;
use Schorts\SharedKernel\EventBus\InMemoryEventBus;

final class UserRegisteredEvent extends DomainEvent
{
  public function getEventName(): string
  {
    return 'user.registered';
  }
}

final class SendWelcomeEmailSubscriber extends AbstractEventSubscriber
{
  public function subscribedTo(): array
  {
    return ['user.registered'];
  }

  public function handle(DomainEvent $event, ?EventSubscriberContext $context = null): void
  {
    $email = $event->getPayload()['email'];
    // Send email ...
  }
}

$eventBus = new InMemoryEventBus();
$eventBus->registerSubscriber(new SendWelcomeEmailSubscriber());

$eventBus->publish(new UserRegisteredEvent('corr-1', ['email' => 'alice@example.com']));
```

### 4. Result Pattern

```php
use Schorts\SharedKernel\Result\Result;

function divide(float $a, float $b): Result
{
  if ($b === 0.0) {
    return Result::failure('Cannot divide by zero');
  }

  return Result::success($a / $b);
}

$result = divide(10.0, 2.0)
  ->map(fn (float $val) => $val * 10);

if ($result->isSuccess()) {
  echo $result->getValue(); // 50
}
```

### 5. Access Control (RBAC & ABAC)

```php
use Schorts\SharedKernel\RBAC\Permission;
use Schorts\SharedKernel\RBAC\RBACPolicy;
use Schorts\SharedKernel\RBAC\BaseResource;

class DocumentPolicy extends RBACPolicy
{
  public function getPermissions(string $role): array
  {
    return match ($role) {
      'admin' => [new Permission('*', '*')],
      'editor' => [new Permission('document', 'write'), new Permission('document', 'read')],
      'viewer' => [new Permission('document', 'read')],
      default => [],
    };
  }
}

$policy = new DocumentPolicy();
$canWrite = $policy->can('editor', 'write', 'document'); // true
```

---

## 🧪 Testing

Run the test suite via PHPUnit:

```bash
composer test
# or
php vendor/bin/phpunit
```

## 📜 License 

LGPL-3.0-or-later
