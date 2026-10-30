<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\EventBus;

class EventSubscriberOptions
{
  public function __construct(
    public readonly bool $logging = false,
    public readonly bool $metrics = false,
    public readonly bool $retryOnError = false,
    public readonly int $maxRetries = 0,
    public readonly int $priority = 0,
  ) {}
}
