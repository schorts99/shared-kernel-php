<?php

namespace Schorts\SharedKernel\CQRS\Query;

use Schorts\SharedKernel\CQRS\Query\QueryMetadata;
use Schorts\SharedKernel\CQRS\Query\QueryPrimitives;

interface Query
{
  public function getType(): string;
  public function getMetadata(): QueryMetadata;
  public function toPrimitives(): QueryPrimitives;
}
