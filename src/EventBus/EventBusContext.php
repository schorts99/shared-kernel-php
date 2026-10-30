<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\EventBus;

use DateTimeImmutable;

class EventBusContext
{
  /**
   * @param array<string, mixed> $metadata
   */
  public function __construct(
    public readonly DateTimeImmutable $startTime,
    public readonly array $metadata,
    public readonly EventBusConfig $config,
    public readonly ?string $correlationId = null,
  ) {}
}
