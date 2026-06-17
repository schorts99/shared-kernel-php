<?php

namespace Schorts\SharedKernel\Criteria;

use Schorts\SharedKernel\Criteria\Direction;

class Order
{
  public function __construct(
    public string $field,
    public Direction $direction
  ) {}
}
