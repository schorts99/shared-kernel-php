<?php

namespace Schorts\SharedKernel\CQRS\QueryHandler\Exceptions;

class QueryValidationError extends \Exception
{
  public readonly string $errorCode;
  public readonly ?array $details;

  public function __construct(
    string $message,
    string $errorCode = 'QUERY_VALIDATION_FAILED',
    ?array $details = null
  ) {
    parent::__construct($message);

    $this->errorCode = $errorCode;
    $this->details = $details;
  }
}
