<?php

namespace Schorts\SharedKernel\Criteria\Exceptions;

use InvalidArgumentException;
use Schorts\SharedKernel\Criteria\Direction;

class OrderNotValid extends InvalidArgumentException
{
  public function __construct(Direction $order)
  {
    parent::__construct("Order not valid: {$order->value}");
  }
}
