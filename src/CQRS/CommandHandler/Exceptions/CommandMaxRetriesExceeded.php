<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\CQRS\CommandHandler\Exceptions;

use RuntimeException;
use Throwable;

class CommandMaxRetriesExceeded extends RuntimeException
{
  public function __construct(
    public readonly string $commandType,
    public readonly int $retries,
    ?Throwable $previous = null,
  ) {
    parent::__construct(
      "Command '{$commandType}' failed after {$retries} retry attempts.",
      0,
      $previous,
    );
  }
}
