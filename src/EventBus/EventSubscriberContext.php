<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\EventBus;

use DateTimeImmutable;

class EventSubscriberContext
{
  /**
   * @param array<string, mixed> $metadata
   */
  public function __construct(
    public readonly DateTimeImmutable $subscriptionTime,
    public readonly array $metadata,
    public readonly EventSubscriberOptions $options,
    public readonly ?string $correlationId = null,
  ) {}
}
