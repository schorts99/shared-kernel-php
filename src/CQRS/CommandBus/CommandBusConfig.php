<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\CQRS\CommandBus;

final class CommandBusConfig
{
  public function __construct(
    public readonly bool $enableMetrics = false,
    public readonly bool $enableLogging = false,
    public readonly int $timeout = 0,
    public readonly int $maxConcurrency = 1,
    public readonly bool $autoPublishEvents = false,
    public readonly bool $transactional = false,
  ) {}
}
