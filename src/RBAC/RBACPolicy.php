<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\RBAC;

use Schorts\SharedKernel\ABAC\Predicate;

abstract class RBACPolicy
{
  /**
   * @return array<Permission>
   */
  abstract public function getPermissions(string $role): array;

  /**
   * @param string|array<string> $roles
   */
  public function can(string|array $roles, string $action, string|BaseResource $resource): bool
  {
    $roleList = is_array($roles) ? $roles : [$roles];
    $resourceName = $resource instanceof BaseResource ? $resource->getResourceName() : $resource;

    foreach ($roleList as $role) {
      $permissions = $this->getPermissions($role);
      foreach ($permissions as $permission) {
        if ($permission->matches($resourceName, $action)) {
          return true;
        }
      }
    }

    return false;
  }

  /**
   * @param string|array<string> $roles
   * @param array<Predicate|callable(mixed, string|BaseResource, mixed): bool> $predicates
   */
  public function canWithAttributes(
    mixed $user,
    string|array $roles,
    string $action,
    string|BaseResource $resource,
    array $predicates,
    mixed $context = null,
  ): bool {
    if (!$this->can($roles, $action, $resource)) {
      return false;
    }

    foreach ($predicates as $predicate) {
      $result = is_callable($predicate)
        ? $predicate($user, $resource, $context)
        : $predicate->evaluate($user, $resource, $context);

      if (!$result) {
        return false;
      }
    }

    return true;
  }

  /**
   * @param string|array<string> $roles
   * @param array<Predicate|callable(mixed, string|BaseResource, mixed): bool> $predicates
   */
  public function canAnyWithAttributes(
    mixed $user,
    string|array $roles,
    string $action,
    string|BaseResource $resource,
    array $predicates,
    mixed $context = null,
  ): bool {
    if (!$this->can($roles, $action, $resource)) {
      return false;
    }

    if (empty($predicates)) {
      return true;
    }

    foreach ($predicates as $predicate) {
      $result = is_callable($predicate)
        ? $predicate($user, $resource, $context)
        : $predicate->evaluate($user, $resource, $context);

      if ($result) {
        return true;
      }
    }

    return false;
  }
}
