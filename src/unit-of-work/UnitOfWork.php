<?php

namespace Schorts\SharedKernel\UnitOfWork;

interface UnitOfWork
{
  public function begin(): void;
  public function commit(): void;
  public function rollback(): void;
  public function isActive(): bool;
}
