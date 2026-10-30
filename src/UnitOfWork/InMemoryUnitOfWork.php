<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\UnitOfWork;

use RuntimeException;

class InMemoryUnitOfWork implements UnitOfWork
{
  private bool $active = false;
  /** @var array<callable> */
  private array $commitHooks = [];
  /** @var array<callable> */
  private array $rollbackHooks = [];

  public function begin(): void
  {
    if ($this->active) {
      throw new RuntimeException('Unit of work already active.');
    }

    $this->active = true;
    $this->commitHooks = [];
    $this->rollbackHooks = [];
  }

  public function commit(): void
  {
    if (!$this->active) {
      throw new RuntimeException('No active unit of work to commit.');
    }

    foreach ($this->commitHooks as $hook) {
      $hook();
    }

    $this->active = false;
    $this->commitHooks = [];
    $this->rollbackHooks = [];
  }

  public function rollback(): void
  {
    if (!$this->active) {
      throw new RuntimeException('No active unit of work to rollback.');
    }

    foreach ($this->rollbackHooks as $hook) {
      $hook();
    }

    $this->active = false;
    $this->commitHooks = [];
    $this->rollbackHooks = [];
  }

  public function isActive(): bool
  {
    return $this->active;
  }

  public function onCommit(callable $hook): void
  {
    $this->commitHooks[] = $hook;
  }

  public function onRollback(callable $hook): void
  {
    $this->rollbackHooks[] = $hook;
  }
}
