<?php

namespace Schorts\SharedKernel\DAO;

use Schorts\SharedKernel\Entity\Entity;
use Schorts\SharedKernel\Criteria\Criteria;
use Schorts\SharedKernel\UnitOfWork\UnitOfWork;
use Schorts\SharedKernel\DAO\DeleteMode;

abstract class DAO
{
  protected DeleteMode $deleteMode;

  public function __construct(DeleteMode $deleteMode = DeleteMode::HARD)
  {
    $this->deleteMode = $deleteMode;
  }

  abstract public function getAll(?UnitOfWork $uow = null): array;
  abstract public function findByID(mixed $id, ?UnitOfWork $uow = null): ?Entity;
  abstract public function findOneBy(Criteria $criteria, ?UnitOfWork $uow = null): ?Entity;
  abstract public function search(Criteria $criteria, ?UnitOfWork $uow = null): array;
  abstract public function countBy(Criteria $criteria, ?UnitOfWork $uow = null): int;
  abstract public function exists(Criteria $criteria, ?UnitOfWork $uow = null): bool;
  abstract public function create(Entity $entity, ?UnitOfWork $uow = null): Entity;
  abstract public function update(Entity $entity, ?UnitOfWork $uow = null): Entity;
  abstract public function save(Entity $entity, ?UnitOfWork $uow = null): Entity;
  abstract public function delete(Entity $entity, ?UnitOfWork $uow = null): Entity;
  abstract public function deleteByID(mixed $id, ?UnitOfWork $uow = null): void;
  abstract public function saveMany(array $entities, ?UnitOfWork $uow = null): array;
  abstract public function restore(Entity $entity, ?UnitOfWork $uow = null): Entity;
}
