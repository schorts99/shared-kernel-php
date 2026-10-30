<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\EventBus;

use DateTimeImmutable;
use Schorts\SharedKernel\DomainEvent\DomainEvent;
use Throwable;

class InMemoryDeadLetterStore implements DeadLetterStore
{
  /** @var array<array{event: DomainEvent, error: string, subscriber: ?string, timestamp: DateTimeImmutable}> */
  private array $entries = [];

  public function store(DomainEvent $event, Throwable $error, ?string $subscriberName = null): void
  {
    $this->entries[] = [
      'event' => $event,
      'error' => $error->getMessage(),
      'subscriber' => $subscriberName,
      'timestamp' => new DateTimeImmutable(),
    ];
  }

  public function getFailedEvents(): array
  {
    return $this->entries;
  }

  public function clear(): void
  {
    $this->entries = [];
  }
}
