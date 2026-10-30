<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\ValueObjects\Exceptions;

use InvalidArgumentException;
use Throwable;

class ValueNotValid extends InvalidArgumentException
{
  public function __construct(
    public readonly string $attributeName,
    string $message = '',
    int $code = 0,
    ?Throwable $previous = null,
  ) {
    $msg = $message !== '' ? $message : sprintf("The value for '%s' is not valid.", $attributeName);
    parent::__construct($msg, $code, $previous);
  }
}
