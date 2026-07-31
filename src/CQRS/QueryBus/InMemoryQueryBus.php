<?php

namespace Schorts\SharedKernel\CQRS\QueryBus;

use Schorts\SharedKernel\CQRS\QueryHandler\QueryHandler;
use Schorts\SharedKernel\CQRS\Query\Exceptions\QueryAlreadyRegistered;
use Schorts\SharedKernel\CQRS\Query\Exceptions\QueryNotRegistered;
use Schorts\SharedKernel\CQRS\Query\Query;
use Schorts\SharedKernel\Exceptions\AggregateError;

class InMemoryQueryBus implements QueryBus
{
  private array $handlers = [];
  private array $middleware = [];
  private QueryBusConfig $config;

  public function __construct()
  {
    $this->config = new QueryBusConfig(
      enableMetrics: false,
      enableCaching: false,
      cacheTtl: 0,
      timeout: 0,
      maxConcurrency: 1,
    );
  }

  public function register(string $type, QueryHandler $handler): void
  {
    if (isset($this->handlers[$type])) {
      throw new QueryAlreadyRegistered($type);
    }

    $this->handlers[$type] = $handler;
  }

  public function unregister(string $type): bool
  {
    if (!isset($this->handlers[$type])) {
      return false;
    }

    unset($this->handlers[$type]);

    return true;
  }

  public function hasHandler(string $type): bool
  {
    return isset($this->handlers[$type]);
  }

  public function getRegisteredTypes(): array
  {
    return array_keys($this->handlers);
  }

  public function dispatch(Query $query): mixed
  {
    $type = $query->getType();
    $handler = $this->handlers[$type] ?? null;

    if ($handler === null) {
      throw new QueryNotRegistered($type);
    }

    $startTime = new \DateTimeImmutable();
    $correlationId = $query->getMetadata()->correlationId;
    $context = new QueryBusContext(
      correlationId: $correlationId,
      startTime: $startTime,
      metadata: $query->getMetadata()->toArray(),
      config: $this->config,
    );

    try {
      foreach ($this->middleware as $mw) {
        $mw->beforeDispatch($query, $context);
      }

      $result = $handler->handle($query);

      foreach ($this->middleware as $mw) {
        $mw->afterDispatch($query, $result, $context);
      }

      return $result;
    } catch (\Throwable $error) {
      foreach ($this->middleware as $mw) {
        $mw->onError($query, $error, $context);
      }

      throw $error;
    }
  }

  public function dispatchMany(array $queries): array
  {
    $results = [];
    $errors = [];

    foreach ($queries as $index => $query) {
      try {
        $results[] = $this->dispatch($query);
      } catch (\Throwable $error) {
        $errors[] = $error;
      }
    }

    if (count($errors) > 0) {
      throw new AggregateError(
        $errors,
        count($errors) . ' query(s) failed during bulk dispatch',
      );
    }

    return $results;
  }

  public function use(QueryBusMiddleware $middleware): void
  {
    $this->middleware[] = $middleware;
  }

  public function removeMiddleware(QueryBusMiddleware $middleware): bool
  {
    $index = array_search($middleware, $this->middleware, true);

    if ($index === false) {
      return false;
    }

    array_splice($this->middleware, $index, 1);

    return true;
  }

  public function getConfig(): QueryBusConfig
  {
    return new QueryBusConfig(
      enableMetrics: $this->config->enableMetrics,
      enableCaching: $this->config->enableCaching,
      cacheTtl: $this->config->cacheTtl,
      timeout: $this->config->timeout,
      maxConcurrency: $this->config->maxConcurrency,
    );
  }

  public function setConfig(QueryBusConfig|array $config): void
  {
    if ($config instanceof QueryBusConfig) {
      $this->config = $config;

      return;
    }

    $this->config = new QueryBusConfig(
      enableMetrics: $config['enableMetrics'] ?? $this->config->enableMetrics,
      enableCaching: $config['enableCaching'] ?? $this->config->enableCaching,
      cacheTtl: $config['cacheTtl'] ?? $this->config->cacheTtl,
      timeout: $config['timeout'] ?? $this->config->timeout,
      maxConcurrency: $config['maxConcurrency'] ?? $this->config->maxConcurrency,
    );
  }

  public function clear(): void
  {
    $this->handlers = [];
    $this->middleware = [];
  }
}
