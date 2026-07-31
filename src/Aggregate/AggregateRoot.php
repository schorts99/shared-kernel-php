<?php

namespace Schorts\SharedKernel\Aggregate;

use Schorts\SharedKernel\ValueObjects\ValueObject;
use Schorts\SharedKernel\DomainEvent\DomainEvent;

abstract class AggregateRoot
{
  private array $domainEvents = [];
  private int $version;
  private bool $uncommittedChanges = false;

  public function __construct(
    public readonly ValueObject $id,
    int $version = 0,
  ) {
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

  public function pullDomainEvents(): array
  {
    $events = [];
    $count = count($this->domainEvents);
    $baseSequence = $this->version - $count;

    foreach ($this->domainEvents as $index => $event) {
      $event->sequenceNumber = $baseSequence + $index + 1;
      $events[] = $event;
    }

    $this->domainEvents = [];

    return $events;
  }

  public function recordDomainEvent(DomainEvent $domainEvent): void
  {
    $this->domainEvents[] = $domainEvent;

    $this->incrementVersion();
  }

  public function equals(AggregateRoot $other): bool
  {
    return $this->id->equals($other->id);
  }

  protected function validateInvariants(): void {}

  protected function validate(): void
  {
    $this->validateInvariants();
  }

  abstract public function toPrimitives(): array;

  public static function fromPrimitives(array $model): static
  {
    $id = $model['id'];
    $version = $model['version'] ?? 0;
    unset($model['id'], $model['version']);

    $instance = new static($id, $version);

    if (method_exists($instance, 'restoreFromPrimitives')) {
      $instance->restoreFromPrimitives($model);
    }

    return $instance;
  }

  protected function restoreFromPrimitives(array $data): void {}

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
