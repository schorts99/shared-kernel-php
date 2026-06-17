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

## 📜 License 

LGPL-3.0-or-later
