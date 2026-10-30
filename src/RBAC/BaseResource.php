<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\RBAC;

interface BaseResource
{
  public function getResourceName(): string;
}
