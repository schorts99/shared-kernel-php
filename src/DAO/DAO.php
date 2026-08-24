<?php

namespace Schorts\SharedKernel\DAO;

use Schorts\SharedKernel\Entity\Entity;
use Schorts\SharedKernel\Criteria\Criteria;
use Schorts\SharedKernel\UnitOfWork\UnitOfWork;

interface DAO
{
  public function getAll(?UnitOfWork $uow = null, bool $includeDeleted = false): array;
  public function findByID(mixed $id, ?UnitOfWork $uow = null, bool $includeDeleted = false): ?Entity;
  public function findOneBy(Criteria $criteria, ?UnitOfWork $uow = null, bool $includeDeleted = false): ?Entity;
  public function search(Criteria $criteria, ?UnitOfWork $uow = null, bool $includeDeleted = false): array;
  public function count(?UnitOfWork $uow = null, bool $includeDeleted = false): int;
  public function countBy(Criteria $criteria, ?UnitOfWork $uow = null, bool $includeDeleted = false): int;
  public function exists(Criteria $criteria, ?UnitOfWork $uow = null, bool $includeDeleted = false): bool;
  public function create(Entity $entity, ?UnitOfWork $uow = null): Entity;
  public function update(Entity $entity, ?UnitOfWork $uow = null): Entity;
  public function save(Entity $entity, ?UnitOfWork $uow = null): Entity;
  public function delete(Entity $entity, ?UnitOfWork $uow = null): Entity;
  public function deleteByID(mixed $id, ?UnitOfWork $uow = null): void;
  public function saveMany(array $entities, ?UnitOfWork $uow = null): array;
  public function restore(Entity $entity, ?UnitOfWork $uow = null): Entity;
}
