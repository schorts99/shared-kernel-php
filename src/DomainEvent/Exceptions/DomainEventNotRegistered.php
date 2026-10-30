<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\DomainEvent\Exceptions;

use RuntimeException;
use Throwable;

class DomainEventNotRegistered extends RuntimeException
{
  public function __construct(string $eventName, int $code = 0, ?Throwable $previous = null)
  {
    parent::__construct("Domain event '{$eventName}' is not registered.", $code, $previous);
  }
}
