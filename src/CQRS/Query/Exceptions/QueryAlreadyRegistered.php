<?php

namespace Schorts\SharedKernel\CQRS\Query\Exceptions;

class QueryAlreadyRegistered extends \Exception
{
  public function __construct(string $query)
  {
    parent::__construct('Query Already Registered: ' . $query);
  }
}
