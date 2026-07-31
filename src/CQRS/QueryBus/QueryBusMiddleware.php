<?php

namespace Schorts\SharedKernel\CQRS\QueryBus;

use Schorts\SharedKernel\CQRS\Query\Query;

interface QueryBusMiddleware
{
  public function beforeDispatch(Query $query, QueryBusContext $context): void;
  public function afterDispatch(Query $query, mixed $result, QueryBusContext $context): void;
  public function onError(Query $query, \Throwable $error, QueryBusContext $context): void;
}
