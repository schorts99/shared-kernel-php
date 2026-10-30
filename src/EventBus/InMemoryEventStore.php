<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\EventBus;

use Schorts\SharedKernel\DomainEvent\DomainEvent;

class InMemoryEventStore implements EventStore
{
  /** @var array<DomainEvent> */
  private array $events = [];

  public function append(DomainEvent $event): void
  {
    $this->events[] = $event;
  }

  public function getEventsFor(string $aggregateId): array
  {
    return array_values(array_filter(
      $this->events,
      static fn (DomainEvent $event): bool =>
        ($event->getPayload()['id'] ?? null) === $aggregateId ||
        ($event->getPayload()['aggregateId'] ?? null) === $aggregateId ||
        $event->getCorrelationId() === $aggregateId
    ));
  }

  public function all(): array
  {
    return $this->events;
  }

  public function clear(): void
  {
    $this->events = [];
  }
}
