<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\UnitOfWork;

use Throwable;

class TransactionalUnitOfWorkRunner implements UnitOfWorkRunner
{
  public function __construct(
    private readonly UnitOfWork $unitOfWork,
  ) {}

  public function run(callable $operation): mixed
  {
    $this->unitOfWork->begin();

    try {
      $result = $operation($this->unitOfWork);
      $this->unitOfWork->commit();
      return $result;
    } catch (Throwable $e) {
      $this->unitOfWork->rollback();
      throw $e;
    }
  }
}
