<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\EventBus;

use Schorts\SharedKernel\DomainEvent\DomainEvent;

interface EventStore
{
  public function append(DomainEvent $event): void;

  /**
   * @return array<DomainEvent>
   */
  public function getEventsFor(string $aggregateId): array;

  /**
   * @return array<DomainEvent>
   */
  public function all(): array;

  public function clear(): void;
}
