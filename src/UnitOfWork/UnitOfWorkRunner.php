<?php

namespace Schorts\SharedKernel\UnitOfWork;

interface UnitOfWorkRunner
{
  public function run(callable $operation): mixed;
}
