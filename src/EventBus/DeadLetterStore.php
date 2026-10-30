<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\EventBus;

use DateTimeImmutable;
use Schorts\SharedKernel\DomainEvent\DomainEvent;
use Throwable;

interface DeadLetterStore
{
  public function store(DomainEvent $event, Throwable $error, ?string $subscriberName = null): void;

  /**
   * @return array<array{event: DomainEvent, error: string, subscriber: ?string, timestamp: DateTimeImmutable}>
   */
  public function getFailedEvents(): array;

  public function clear(): void;
}
