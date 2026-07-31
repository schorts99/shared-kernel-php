<?php

namespace Schorts\SharedKernel\CQRS\QueryHandler;

use Schorts\SharedKernel\CQRS\Query\Query;
use Schorts\SharedKernel\CQRS\QueryHandler\QueryHandlerContext;
use Schorts\SharedKernel\CQRS\QueryHandler\QueryHandlerOptions;

interface QueryHandler
{
  public function handle(Query $query, ?QueryHandlerContext $context = null): mixed;
  public function getOptions(): QueryHandlerOptions;
  public function validate(Query $query): void;
  public function authorize(Query $query): void;
  public function execute(Query $query, ?QueryHandlerContext $context = null): mixed;
  public function getCacheKey(Query $query): ?string;
  public function shouldCache(Query $query, mixed $result): bool;
  public function getCacheTags(Query $query, mixed $result): array;
  public function serializeResult(mixed $result): mixed;
  public function deserializeResult(mixed $payload): mixed;
}
