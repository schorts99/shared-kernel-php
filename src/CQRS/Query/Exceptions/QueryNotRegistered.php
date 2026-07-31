<?php

namespace Schorts\SharedKernel\CQRS\Query\Exceptions;

class QueryNotRegistered extends \Exception
{
  public function __construct(string $query)
  {
    parent::__construct('Query Not Registered: ' . $query);
  }
}
