<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\CQRS\CommandHandler;

use Schorts\SharedKernel\Cache\Cache;
use Schorts\SharedKernel\Idempotency\IdempotencyStore;
use Schorts\SharedKernel\Logger\Logger;

final class CommandHandlerOptions
{
  public function __construct(
    public readonly bool $logging = false,
    public readonly bool $metrics = false,
    public readonly int $maxRetries = 0,
    public readonly bool $idempotent = false,
    public readonly bool $publishEvents = false,
    public readonly ?int $timeout = null,
    public readonly ?Cache $cache = null,
    public readonly ?IdempotencyStore $idempotencyStore = null,
    public readonly ?Logger $logger = null,
  ) {}
}
