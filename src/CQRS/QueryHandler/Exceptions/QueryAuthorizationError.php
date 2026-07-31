<?php

namespace Schorts\SharedKernel\CQRS\QueryHandler\Exceptions;

class QueryAuthorizationError extends \Exception
{
  public readonly string $code;
  public readonly ?array $details;

  public function __construct(
    string $message,
    string $code = 'QUERY_AUTHORIZATION_FAILED',
    ?array $details = null
  ) {
    parent::__construct($message);

    $this->code = $code;
    $this->details = $details;
  }
}
