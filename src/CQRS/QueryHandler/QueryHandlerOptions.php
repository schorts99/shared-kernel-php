<?php

namespace Schorts\SharedKernel\CQRS\QueryHandler;

use Schorts\SharedKernel\Logger\Logger;
use Schorts\SharedKernel\Cache\Cache;

final readonly class QueryHandlerOptions
{
  public function __construct(
    public readonly ?bool $cache = null,
    public readonly ?int $cacheTtl = null,
    public readonly ?bool $logging = null,
    public readonly ?bool $metrics = null,
    public readonly ?int $timeout = null,
    public readonly ?Cache $cacheStore = null,
    public readonly ?Logger $logger = null,
  ) {}
}
