<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\CQRS\CommandBus;

use DateTimeImmutable;
use Schorts\SharedKernel\DomainEvent\DomainEvent;

final class CommandBusContext
{
  /**
   * @param array<string, mixed> $metadata
   * @param array<DomainEvent> $events
   */
  public function __construct(
    public readonly string $correlationId,
    public readonly DateTimeImmutable $startTime,
    public readonly array $metadata,
    public readonly CommandBusConfig $config,
    public array $events = [],
  ) {}
}
