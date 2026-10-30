<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\Tests\UnitOfWork;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Schorts\SharedKernel\UnitOfWork\InMemoryUnitOfWork;
use Schorts\SharedKernel\UnitOfWork\TransactionalUnitOfWorkRunner;

final class UnitOfWorkTest extends TestCase
{
  public function testCommitLifecycle(): void
  {
    $uow = new InMemoryUnitOfWork();
    $committed = false;

    $this->assertFalse($uow->isActive());
    $uow->begin();
    $this->assertTrue($uow->isActive());

    $uow->onCommit(function () use (&$committed) {
      $committed = true;
    });

    $uow->commit();
    $this->assertFalse($uow->isActive());
    $this->assertTrue($committed);
  }

  public function testRollbackLifecycle(): void
  {
    $uow = new InMemoryUnitOfWork();
    $rolledBack = false;

    $uow->begin();
    $uow->onRollback(function () use (&$rolledBack) {
      $rolledBack = true;
    });

    $uow->rollback();
    $this->assertFalse($uow->isActive());
    $this->assertTrue($rolledBack);
  }

  public function testTransactionalRunner(): void
  {
    $uow = new InMemoryUnitOfWork();
    $runner = new TransactionalUnitOfWorkRunner($uow);

    $result = $runner->run(function () {
      return 'success_val';
    });

    $this->assertSame('success_val', $result);
    $this->assertFalse($uow->isActive());

    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage('Failure in transaction');

    $runner->run(function () {
      throw new RuntimeException('Failure in transaction');
    });

    $this->assertFalse($uow->isActive());
  }
}
