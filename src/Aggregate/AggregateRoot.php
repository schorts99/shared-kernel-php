<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\Aggregate;

use RuntimeException;
use Schorts\SharedKernel\DomainEvent\DomainEvent;
use Schorts\SharedKernel\Entity\Entity;
use Schorts\SharedKernel\ValueObjects\ValueObject;

abstract class AggregateRoot extends Entity
{
  /** @var array<DomainEvent> */
  private array $recordedEvents = [];
  private int $version;
  private bool $uncommittedChanges = false;

  public function __construct(
    ValueObject $id,
    int $version = 0,
  ) {
    parent::__construct($id);
    $this->version = $version;
  }

  public function getVersion(): int
  {
    return $this->version;
  }

  protected function incrementVersion(): void
  {
    $this->version++;
    $this->uncommittedChanges = true;
  }

  public function hasUncommittedChanges(): bool
  {
    return $this->uncommittedChanges;
  }

  public function markChangesCommitted(): void
  {
    $this->uncommittedChanges = false;
  }

  /**
   * @return array<DomainEvent>
   */
  public function pullDomainEvents(): array
  {
    $events = [];
    $count = count($this->recordedEvents);
    $baseSequence = $this->version - $count;

    foreach ($this->recordedEvents as $index => $event) {
      $event->sequenceNumber = $baseSequence + $index + 1;
      $events[] = $event;
    }

    $this->recordedEvents = [];

    return $events;
  }

  public function recordDomainEvent(DomainEvent $domainEvent): void
  {
    $this->recordedEvents[] = $domainEvent;
    $this->incrementVersion();
  }

  public function clearDomainEvents(): void
  {
    if (count($this->recordedEvents) > 0) {
      $this->version = max(0, $this->version - count($this->recordedEvents));
      $this->recordedEvents = [];
    }
  }

  public function equals(mixed $other): bool
  {
    if ($other === null || !$other instanceof AggregateRoot) {
      return false;
    }

    if (get_class($this) !== get_class($other)) {
      return false;
    }

    if ($this === $other) {
      return true;
    }

    return $this->id->equals($other->id);
  }

  protected function validateInvariants(): void {}

  protected function validate(): void
  {
    $this->validateInvariants();
  }

  abstract public function toPrimitives(): array;

  public static function fromPrimitives(array $primitives): static
  {
    throw new RuntimeException('Aggregate reconstruction not implemented.');
  }

  protected function restoreFromPrimitives(array $primitives): void {}

  /**
   * @return array{id: ValueObject, version: int, data: array}
   */
  public function toSnapshot(): array
  {
    return [
      'id'      => $this->id,
      'version' => $this->version,
      'data'    => $this->toPrimitives(),
    ];
  }

  public static function fromSnapshot(array $snapshot): static
  {
    $instance = new static($snapshot['id'], $snapshot['version']);

    if (method_exists($instance, 'restoreFromPrimitives')) {
      $instance->restoreFromPrimitives($snapshot['data']);
    }

    $instance->markChangesCommitted();

    return $instance;
  }
}
