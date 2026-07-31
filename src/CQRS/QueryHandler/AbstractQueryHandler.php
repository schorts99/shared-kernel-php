<?php

namespace Schorts\SharedKernel\CQRS\QueryHandler;

use Schorts\SharedKernel\CQRS\Query\Query;
use Schorts\SharedKernel\CQRS\QueryHandler\QueryHandler;
use Schorts\SharedKernel\CQRS\QueryHandler\QueryHandlerOptions;
use Schorts\SharedKernel\CQRS\QueryHandler\QueryHandlerContext;
use Schorts\SharedKernel\CQRS\QueryHandler\Exceptions\QueryAuthorizationError;
use Schorts\SharedKernel\CQRS\QueryHandler\Exceptions\QueryExecutionError;
use Schorts\SharedKernel\CQRS\QueryHandler\Exceptions\QueryValidationError;
use Schorts\SharedKernel\Cache\Cache;
use Schorts\SharedKernel\Logger\Logger;

abstract class AbstractQueryHandler implements QueryHandler
{
  protected readonly QueryHandlerOptions $options;
  protected readonly ?Cache $cache;
  protected readonly ?Logger $logger;

  public function __construct(?QueryHandlerOptions $options = null)
  {
    $this->options = new QueryHandlerOptions(
      cache: $options?->cache ?? false,
      cacheTtl: $options?->cacheTtl ?? 5 * 60 * 1000,
      logging: $options->logging ?? false,
      metrics: $options->metrics ?? false,
      timeout: $options->timeout,
      cacheStore: $options->cacheStore,
      logger: $options->logger,
    );
    $this->cache = $options->cache;
    $this->logger = $options->logger;
  }

  public function handle(Query $query, ?QueryHandlerContext $context = null): mixed
  {
    $startTime = new \DateTimeImmutable();
    $correlationId = $query->getMetadata()->correlationId;
    $handlerContext = new QueryHandlerContext(
      metadata: $query->getMetadata(),
      options: $this->options,
      startTime: $startTime,
      correlationId: $correlationId,
    );

    if ($context !== null) {
      $handlerContext = new QueryHandlerContext(
        metadata: $context->metadata,
        options: $context->options,
        startTime: $context->startTime,
        correlationId: $context->correlationId,
      );
    }

    try {
      $this->validate($query);
      $this->authorize($query);

      if ($this->options->cache && $this->cache !== null) {
        $cacheKey = $this->getCacheKey($query);

        if ($cacheKey !== null) {
          $cachedResult = $this->cache->get($cacheKey);

          if ($cachedResult !== null) {
            if ($this->options->logging) {
              $this->logQuery($query, $cachedResult, $startTime, true);
            }

            return $this->deserializeResult($cachedResult);
          }
        }
      }

      $result = $this->execute($query, $handlerContext);

      if (
        $this->options->cache
        && $this->cache !== null
        && $this->shouldCache($query, $result)
      ) {
        $cacheKey = $this->getCacheKey($query);

        if ($cacheKey !== null) {
          $tags = $this->getCacheTags($query, $result);

          $this->cache->set(
            $cacheKey,
            $this->serializeResult($result),
            $this->options->cacheTtl,
            $tags,
          );
        }
      }

      if ($this->options->logging) {
        $this->logQuery($query, $result, $startTime);
      }

      return $result;
    } catch (\Throwable $error) {
      if ($this->options->logging) {
        $this->logError($query, $error, $startTime);
      }

      if (
        $error instanceof QueryValidationError
        || $error instanceof QueryAuthorizationError
        || $error instanceof QueryExecutionError
      ) {
        throw $error;
      }

      throw new QueryExecutionError(
        'Query execution failed: ' . $error->getMessage(),
        'QUERY_EXECUTION_FAILED',
        ['originalError' => $error],
      );
    }
  }

  public function getOptions(): QueryHandlerOptions
  {
    return $this->options;
  }

  public function validate(Query $query): void {}

  public function authorize(Query $query): void {}

  abstract public function execute(Query $query, ?QueryHandlerContext $context = null): mixed;

  public function getCacheKey(Query $query): ?string
  {
    $primitives = method_exists($query, 'toPrimitives')
      ? $query->toPrimitives()
      : [];

    return $query->getType() . ':' . json_encode($primitives);
  }

  public function shouldCache(Query $query, mixed $result): bool
  {
    return true;
  }

  public function getCacheTags(Query $query, mixed $result): array
  {
    return [];
  }

  public function serializeResult(mixed $result): mixed
  {
    return $result;
  }

  public function deserializeResult(mixed $payload): mixed
  {
    return $payload;
  }

  private function logError(
    Query $query,
    \Throwable $error,
    \DateTimeInterface $startTime
  ) : void {
    $duration = (int) ((microtime(true) * 1000) - ((float) $startTime->format('U.u') * 1000));

    $this->logger?->error(
      sprintf('[Query %s] failed after %dms', $query->getType(), $duration),
      [
        'correlationId' => $query->getMetadata()->correlationId,
        'userId'        => $query->getMetadata()->userId,
        'duration'      => $duration,
        'error'         => $error->getMessage(),
      ],
    );
  }

  private function logQuery(
    Query $query,
    mixed $result,
    \DateTimeInterface $startTime,
    bool $cached = false,
  ): void {
    $duration = (int) ((microtime(true) * 1000) - ((float) $startTime->format('U.u') * 1000));

    $this->logger?->info(
      sprintf(
        '[Query %s] %s in %dms',
        $query->getType(),
        $cached ? 'served from cache' : 'completed',
        $duration,  
      ),
      [
        'correlationId' => $query->getMetadata()->correlationId,
        'userId'        => $query->getMetadata()->userId,
        'resultSize'    => $this->getResultSize($result),
        'cached'        => $cached,
        'duration'      => $duration,
      ],
    );
  }

  private function getResultSize(mixed $result): string
  {
    try {
      $size = strlen(json_encode($result) ?: '');

      if ($size < 1024) {
        return "{$size} bytes";
      }

      if ($size < 1024 * 1024) {
        return round($size / 1024) . ' KB';
      }

      return round($size / (1024 * 1024)) . ' MB';
    } catch (\Throwable) {
      return 'unknown';
    }
  }
}
