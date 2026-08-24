<?php

namespace Schorts\SharedKernel\CQRS\QueryBus;

class QueryBusConfig
{
  public function __construct(
    public readonly ?bool $enableMetrics = null,
    public readonly ?bool $enableCaching = null,
    public readonly ?int $cacheTtl = null,
    public readonly ?int $timeout = null,
    public readonly ?int $maxConcurrency = null,
  ) {}
}
