<?php

namespace Schorts\SharedKernel\Criteria\Exceptions;

use InvalidArgumentException;
use Schorts\SharedKernel\Criteria\Operator;

class OperatorNotValid extends InvalidArgumentException
{
  public function __construct(Operator $operator)
  {
    parent::__construct("Operator not valid: {$operator->value}");
  }
}
