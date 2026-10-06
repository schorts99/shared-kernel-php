<?php

namespace Schorts\SharedKernel\Entity;

use Schorts\SharedKernel\Entity\Exceptions\EntityNotRegistered;

final class EntityRegistry
{
  private static array $registry = [];

  public static function register(string $entityName, string $className): void
  {
    self::$registry[$entityName] = $className;
  }

  public static function fromPrimitives(string $entityName, array $primitives): Entity
  {
    if (!isset(self::$registry[$entityName])) {
      throw new EntityNotRegistered($entityName);
    }

    $className = self::$registry[$entityName];

    if (!is_subclass_of($className, Entity::class)) {
      throw new \InvalidArgumentException("$className must extend Entity");
    }

    return $className::fromPrimitives($primitives);
  }
}
