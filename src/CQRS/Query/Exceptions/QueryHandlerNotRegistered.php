<?php

namespace Schorts\SharedKernel\CQRS\Query\Exceptions;

class QueryHandlerNotRegistered extends \Exception
{
  public function __construct(string $query)
  {
    parent::__construct('QueryHandler Not Registered: ' . $query);
  }
}
