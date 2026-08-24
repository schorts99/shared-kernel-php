<?php

namespace Schorts\SharedKernel\Criteria\Exceptions;

use InvalidArgumentException;

class OffsetNotValid extends InvalidArgumentException
{
  public function __construct(int $offset)
  {
    parent::__construct("Offset not valid: $offset");
  }
}
