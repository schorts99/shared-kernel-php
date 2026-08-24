<?php

namespace Schorts\SharedKernel\CQRS\Query;

use Schorts\SharedKernel\CQRS\Query\Exceptions\QueryNotRegistered;

use DateTimeImmutable;
use Exception;

final class QueryRegistry
{
  private static array $registry = [];

  public static function register(string $queryType, string $constructor): void
  {
    self::$registry[$queryType] = $constructor;
  }

  /**
   * @throws QueryNotRegistered
   * @throws Exception
   */
  public static function fromPrimitives(QueryPrimitives $primitives): Query
  {
    $constructor = self::$registry[$primitives->type] ?? null;

    if ($constructor === null) {
      throw new QueryNotRegistered($primitives->type);
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
