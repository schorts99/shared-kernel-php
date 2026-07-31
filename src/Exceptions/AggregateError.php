<?php

namespace Schorts\SharedKernel\Exceptions;

class AggregateError extends \Exception
{
  private array $errors = [];

  public function __construct(array $errors, string $message)
  {
    parent::__construct($message);

    $this->errors = $errors;
  }
}
