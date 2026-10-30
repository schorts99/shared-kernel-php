<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\CQRS\Command\Exceptions;

use RuntimeException;
use Throwable;

class CommandNotRegistered extends RuntimeException
{
  public function __construct(string $commandType, int $code = 0, ?Throwable $previous = null)
  {
    parent::__construct("Command '{$commandType}' is not registered.", $code, $previous);
  }
}
