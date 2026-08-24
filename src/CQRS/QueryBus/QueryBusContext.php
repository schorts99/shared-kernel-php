<?php

namespace Schorts\SharedKernel\CQRS\QueryBus;

class QueryBusContext
{
  public function __construct(
    public readonly string $correlationId,
    public readonly \DateTimeInterface $startTime,
    public readonly array $metadata,
    public readonly QueryBusConfig $config,
  ) {}
}
