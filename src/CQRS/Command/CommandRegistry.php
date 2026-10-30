<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\CQRS\Command;

use DateTimeImmutable;
use InvalidArgumentException;
use Schorts\SharedKernel\CQRS\Command\Exceptions\CommandNotRegistered;

final class CommandRegistry
{
  /** @var array<string, string|callable(CommandPrimitives): Command> */
  private static array $registry = [];

  /**
   * @param string|callable(CommandPrimitives): Command $constructorOrFactory
   */
  public static function register(string $commandType, string|callable $constructorOrFactory): void
  {
    self::$registry[$commandType] = $constructorOrFactory;
  }

  public static function has(string $commandType): bool
  {
    return isset(self::$registry[$commandType]);
  }

  public static function unregister(string $commandType): bool
  {
    if (!isset(self::$registry[$commandType])) {
      return false;
    }

    unset(self::$registry[$commandType]);
    return true;
  }

  public static function clear(): void
  {
    self::$registry = [];
  }

  public static function fromPrimitives(CommandPrimitives|array $primitives): Command
  {
    if (is_array($primitives)) {
      $primitives = new CommandPrimitives(
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

    $commandType = $primitives->type;

    if (!isset(self::$registry[$commandType])) {
      throw new CommandNotRegistered($commandType);
    }

    $constructor = self::$registry[$commandType];

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
