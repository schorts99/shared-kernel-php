<?php

namespace Schorts\SharedKernel\Entity\Exceptions;

class EntityNotRegistered extends \RuntimeException
{
  public function __construct(string $entityName)
  {
    parent::__construct("Entity not registered: $entityName");
  }
}
