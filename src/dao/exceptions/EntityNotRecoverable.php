<?php

namespace Schorts\SharedKernel\DAO\Exceptions;

class EntityNotRecoverable extends \RuntimeException
{
    public function __construct(string $message = "Entity cannot be recovered because it was hard deleted.")
    {
      parent::__construct($message);
    }
}
