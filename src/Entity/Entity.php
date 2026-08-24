<?php

namespace Schorts\SharedKernel\Entity;

use Schorts\SharedKernel\ValueObjects\ValueObject;
use Schorts\SharedKernel\Model\Model;
use Schorts\SharedKernel\DomainEvent\DomainEvent;

abstract class Entity
{
  protected ValueObject $id;
  private array $domainEvents = [];

  public function __construct(ValueObject $id)
  {
    $this->id = $id;
  }

  public function getId(): ValueObject
  {
    return $this->id;
  }

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

  abstract public function toPrimitives(): Model;

  public static function fromPrimitives(Model $model): Entity
  {
    throw new \RuntimeException("Entity reconstruction not implemented.");
  }
}
