<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\Entity;

use Schorts\SharedKernel\DomainEvent\DomainEvent;
use Schorts\SharedKernel\ValueObjects\ValueObject;

abstract class Entity
{
  private array $domainEvents = [];

  public function __construct(
    public readonly ValueObject $id,
  ) {}

  public function getId(): ValueObject
  {
    return $this->id;
  }

  /**
   * @return array<DomainEvent>
   */
  public function pullDomainEvents(): array
  {
    $events = $this->domainEvents;
    $this->domainEvents = [];

    return $events;
  }

  public function recordDomainEvent(DomainEvent $domainEvent): void
  {
    $this->domainEvents[] = $domainEvent;
  }

  public function clearDomainEvents(): void
  {
    $this->domainEvents = [];
  }

  public function equals(mixed $other): bool
  {
    if ($other === null) {
      return false;
    }

    if (!$other instanceof Entity) {
      return false;
    }

    if ($this === $other) {
      return true;
    }

    return $this->id->equals($other->getId());
  }

  abstract public function toPrimitives(): array;

  public static function fromPrimitives(array $primitives): Entity
  {
    throw new \RuntimeException('Entity reconstruction not implemented.');
  }
}
