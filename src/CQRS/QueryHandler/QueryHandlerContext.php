<?php

namespace Schorts\SharedKernel\CQRS\QueryHandler;

use Schorts\SharedKernel\CQRS\Query\QueryMetadata;
use Schorts\SharedKernel\CQRS\QueryHandler\QueryHandlerOptions;

final readonly class QueryHandlerContext
{
  public function __construct(
    public QueryMetadata $metadata,
    public QueryHandlerOptions $options,
    public readonly \DateTimeInterface $startTime,
    public readonly string $correlationId,
  ) {}
}
