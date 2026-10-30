<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\RBAC;

use JsonSerializable;

class Permission implements JsonSerializable
{
  public function __construct(
    public readonly string $resource,
    public readonly string $action,
  ) {}

  public static function create(string $resource, string $action): self
  {
    return new self($resource, $action);
  }

  public function matches(string $resource, string $action): bool
  {
    $resourceMatches = $this->resource === '*' || $this->resource === $resource;
    $actionMatches = $this->action === '*' || $this->action === 'manage' || $this->action === $action;

    return $resourceMatches && $actionMatches;
  }

  /**
   * @return array{resource: string, action: string}
   */
  public function toArray(): array
  {
    return [
      'resource' => $this->resource,
      'action' => $this->action,
    ];
  }

  public function jsonSerialize(): mixed
  {
    return $this->toArray();
  }
}
