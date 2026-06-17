<?php

namespace Schorts\SharedKernel\Criteria;

use Schorts\SharedKernel\Criteria\Operator;

class Filter
{
  public function __construct(
    public string $field,
    public Operator $operator,
    public mixed $value
  ) {}
}
