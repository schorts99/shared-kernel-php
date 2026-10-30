<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\CQRS\Query;

use DateTimeImmutable;
use Schorts\SharedKernel\CQRS\Query\Exceptions\QueryNotRegistered;

final class QueryRegistry
{
  /** @var array<string, string|callable(QueryPrimitives): Query> */
  private static array $registry = [];

  /**
   * @param string|callable(QueryPrimitives): Query $constructorOrFactory
   */
  public static function register(string $queryType, string|callable $constructorOrFactory): void
  {
    self::$registry[$queryType] = $constructorOrFactory;
  }

  public static function has(string $queryType): bool
  {
    return isset(self::$registry[$queryType]);
  }

  public static function unregister(string $queryType): bool
  {
    if (!isset(self::$registry[$queryType])) {
      return false;
    }

    unset(self::$registry[$queryType]);
    return true;
  }

  public static function clear(): void
  {
    self::$registry = [];
  }

  public static function fromPrimitives(QueryPrimitives|array $primitives): Query
  {
    if (is_array($primitives)) {
      $primitives = new QueryPrimitives(
        id: (string) ($primitives['id'] ?? ''),
        type: (string) ($primitives['type'] ?? ''),
        created_at: (string) ($primitives['created_at'] ?? $primitives['createdAt'] ?? ''),
        correlation_id: (string) ($primitives['correlation_id'] ?? $primitives['correlationId'] ?? ''),
        causation_id: $primitives['causation_id'] ?? $primitives['causationId'] ?? null,
        request_id: $primitives['request_id'] ?? $primitives['requestId'] ?? null,
        version: (int) ($primitives['version'] ?? 1),
        user_id: $primitives['user_id'] ?? $primitives['userId'] ?? null,
        tenant_id: $primitives['tenant_id'] ?? $primitives['tenantId'] ?? null,
        payload: $primitives['payload'] ?? null,
        headers: $primitives['headers'] ?? null,
        context: $primitives['context'] ?? null,
      );
    }

    $queryType = $primitives->type;

    if (!isset(self::$registry[$queryType])) {
      throw new QueryNotRegistered($queryType);
    }

    $constructor = self::$registry[$queryType];

    if (is_callable($constructor)) {
      return $constructor($primitives);
    }

    if (method_exists($constructor, 'fromPrimitives')) {
      return $constructor::fromPrimitives($primitives);
    }

    return new $constructor(
      correlationId: $primitives->correlation_id,
      payload: $primitives->payload,
      customMetadata: [
        'id'            => $primitives->id,
        'createdAt'     => new DateTimeImmutable($primitives->created_at),
        'correlationId' => $primitives->correlation_id,
        'causationId'   => $primitives->causation_id,
        'requestId'     => $primitives->request_id,
        'version'       => $primitives->version,
        'userId'        => $primitives->user_id,
        'tenantId'      => $primitives->tenant_id,
        'headers'       => $primitives->headers,
        'context'       => $primitives->context,
      ],
    );
  }
}
