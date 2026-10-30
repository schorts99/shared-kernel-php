<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\CQRS\CommandHandler\Exceptions;

use RuntimeException;
use Throwable;

class CommandAuthorizationError extends RuntimeException
{
  public function __construct(
    public readonly string $commandType,
    string $message = '',
    int $code = 0,
    ?Throwable $previous = null,
  ) {
    $msg = $message !== '' ? $message : "Authorization failed for command '{$commandType}'.";
    parent::__construct($msg, $code, $previous);
  }
}
