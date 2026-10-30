<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\EventBus;

class EventBusConfig
{
  public function __construct(
    public readonly bool $enableLogging = false,
    public readonly bool $enableMetrics = false,
    public readonly bool $autoReplay = false,
    public readonly int $maxRetries = 0,
  ) {}
}
