<?php

namespace Schorts\SharedKernel\CQRS\QueryBus;

use Schorts\SharedKernel\CQRS\QueryHandler\QueryHandler;
use Schorts\SharedKernel\CQRS\Query\Query;

interface QueryBus
{
  public function register(string $type, QueryHandler $handler): void;
  public function unregister(string $type): bool;
  public function hasHandler(string $type): bool;
  public function getRegisteredTypes(): array;
  public function dispatch(Query $query): mixed;
  public function dispatchMany(array $queries): array;
  public function use(QueryBusMiddleware $middleware): void;
  public function removeMiddleware(QueryBusMiddleware $middleware): bool;
  public function getConfig(): QueryBusConfig;
  public function setConfig(QueryBusConfig $config): void;
  public function clear(): void;
}
