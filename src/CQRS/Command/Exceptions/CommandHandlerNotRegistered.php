<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\CQRS\Command\Exceptions;

use RuntimeException;
use Throwable;

class CommandHandlerNotRegistered extends RuntimeException
{
  public function __construct(string $commandType, int $code = 0, ?Throwable $previous = null)
  {
    parent::__construct("No handler registered for command type '{$commandType}'.", $code, $previous);
  }
}
