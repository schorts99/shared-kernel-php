<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\ABAC;

use Schorts\SharedKernel\RBAC\BaseResource;

interface Predicate
{
  public function evaluate(mixed $user, string|BaseResource $resource, mixed $context = null): bool;
}
