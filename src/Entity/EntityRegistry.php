<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\Entity;

use InvalidArgumentException;
use Schorts\SharedKernel\Entity\Exceptions\EntityNotRegistered;

final class EntityRegistry
{
  /** @var array<string, string|callable(array): Entity> */
  private static array $registry = [];

  /**
   * @param string|callable(array): Entity $constructorOrFactory
   */
  public static function register(string $entityName, string|callable $constructorOrFactory): void
  {
    self::$registry[$entityName] = $constructorOrFactory;
  }

  public static function has(string $entityName): bool
  {
    return isset(self::$registry[$entityName]);
  }

  public static function unregister(string $entityName): bool
  {
    if (!isset(self::$registry[$entityName])) {
      return false;
    }

    unset(self::$registry[$entityName]);
    return true;
  }

  public static function clear(): void
  {
    self::$registry = [];
  }

  public static function fromPrimitives(string $entityName, array $primitives): Entity
  {
    if (!isset(self::$registry[$entityName])) {
      throw new EntityNotRegistered($entityName);
    }

    $registered = self::$registry[$entityName];

    if (is_callable($registered)) {
      return $registered($primitives);
    }

    if (!is_subclass_of($registered, Entity::class)) {
      throw new InvalidArgumentException("{$registered} must extend Entity");
    }

    return $registered::fromPrimitives($primitives);
  }
}
