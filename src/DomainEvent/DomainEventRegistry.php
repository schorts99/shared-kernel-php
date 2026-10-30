<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\DomainEvent;

use DateTimeImmutable;
use InvalidArgumentException;
use Schorts\SharedKernel\DomainEvent\Exceptions\DomainEventNotRegistered;

final class DomainEventRegistry
{
  /** @var array<string, string|callable(DomainEventPrimitives): DomainEvent> */
  private static array $registry = [];

  /**
   * @param string|callable(DomainEventPrimitives): DomainEvent $constructorOrFactory
   */
  public static function register(string $eventName, string|callable $constructorOrFactory): void
  {
    self::$registry[$eventName] = $constructorOrFactory;
  }

  public static function has(string $eventName): bool
  {
    return isset(self::$registry[$eventName]);
  }

  public static function unregister(string $eventName): bool
  {
    if (!isset(self::$registry[$eventName])) {
      return false;
    }

    unset(self::$registry[$eventName]);
    return true;
  }

  public static function clear(): void
  {
    self::$registry = [];
  }

  public static function fromPrimitives(DomainEventPrimitives|array $primitives): DomainEvent
  {
    if (is_array($primitives)) {
      $primitives = new DomainEventPrimitives(
        id: (string) ($primitives['id'] ?? ''),
        type: (string) ($primitives['type'] ?? ''),
        occurredAt: (string) ($primitives['occurredAt'] ?? $primitives['occurred_at'] ?? ''),
        correlationId: (string) ($primitives['correlationId'] ?? $primitives['correlation_id'] ?? ''),
        causationId: $primitives['causationId'] ?? $primitives['causation_id'] ?? null,
        requestId: $primitives['requestId'] ?? $primitives['request_id'] ?? null,
        version: (int) ($primitives['version'] ?? 1),
        userId: $primitives['userId'] ?? $primitives['user_id'] ?? null,
        tenantId: $primitives['tenantId'] ?? $primitives['tenant_id'] ?? null,
        payload: (array) ($primitives['payload'] ?? []),
        meta: (array) ($primitives['meta'] ?? [])
      );
    }

    $eventName = $primitives->type;

    if (!isset(self::$registry[$eventName])) {
      throw new DomainEventNotRegistered($eventName);
    }

    $registered = self::$registry[$eventName];

    if (is_callable($registered)) {
      return $registered($primitives);
    }

    if (method_exists($registered, 'fromPrimitives')) {
      return $registered::fromPrimitives($primitives);
    }

    $meta = $primitives->meta;
    $customMetadata = new DomainEventMetadata(
      id: $primitives->id,
      occurredAt: new DateTimeImmutable($primitives->occurredAt),
      correlationId: $primitives->correlationId,
      causationId: $primitives->causationId,
      requestId: $primitives->requestId,
      version: $primitives->version,
      userId: $primitives->userId,
      tenantId: $primitives->tenantId,
      retries: (int) ($meta['retries'] ?? 0),
      headers: $meta['headers'] ?? null,
      context: $meta['context'] ?? null,
    );

    return new $registered(
      correlationId: $primitives->correlationId,
      payload: $primitives->payload,
      customMetadata: $customMetadata,
    );
  }
}
