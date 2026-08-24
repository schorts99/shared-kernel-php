<?php

namespace Schorts\SharedKernel\Criteria\Exceptions;

use InvalidArgumentException;

class LimitNotValid extends InvalidArgumentException
{
  public function __construct(int $limit)
  {
    parent::__construct("Limit not valid: $limit");
  }
}
