<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\DAO;

use DateTimeImmutable;
use Schorts\SharedKernel\Criteria\Criteria;
use Schorts\SharedKernel\Criteria\Direction;
use Schorts\SharedKernel\Criteria\Filter;
use Schorts\SharedKernel\Criteria\Operator;
use Schorts\SharedKernel\Criteria\Order;
use Schorts\SharedKernel\DAO\Exceptions\EntityNotRecoverable;
use Schorts\SharedKernel\Entity\Entity;
use Schorts\SharedKernel\UnitOfWork\UnitOfWork;

class InMemoryDAO implements DAO
{
  /** @var array<string, Entity> */
  protected array $entities = [];
  /** @var array<string, string> */
  protected array $softDeletedAt = [];
  protected DeleteMode $deleteMode;

  /**
   * @param array<Entity> $initialData
   */
  public function __construct(
    DeleteMode $deleteMode = DeleteMode::HARD,
    array $initialData = [],
  ) {
    $this->deleteMode = $deleteMode;

    foreach ($initialData as $entity) {
      $id = (string) $entity->getId();
      $this->entities[$id] = $entity;
    }
  }

  public function getAll(?UnitOfWork $uow = null, bool $includeDeleted = false): array
  {
    $result = [];

    foreach ($this->entities as $id => $entity) {
      if (!$includeDeleted && isset($this->softDeletedAt[$id])) {
        continue;
      }
      $result[] = $entity;
    }

    return $result;
  }

  public function findByID(mixed $id, ?UnitOfWork $uow = null, bool $includeDeleted = false): ?Entity
  {
    $key = (string) $id;

    if (!isset($this->entities[$key])) {
      return null;
    }

    if (!$includeDeleted && isset($this->softDeletedAt[$key])) {
      return null;
    }

    return $this->entities[$key];
  }

  public function findOneBy(Criteria $criteria, ?UnitOfWork $uow = null, bool $includeDeleted = false): ?Entity
  {
    $results = $this->search($criteria, $uow, $includeDeleted);

    return $results[0] ?? null;
  }

  public function search(Criteria $criteria, ?UnitOfWork $uow = null, bool $includeDeleted = false): array
  {
    $all = $this->getAll($uow, $includeDeleted);

    // Apply filters
    $filtered = array_filter($all, function (Entity $entity) use ($criteria): bool {
      $primitives = $entity->toPrimitives();

      foreach ($criteria->filters() as $filter) {
        if (!$this->matchesFilter($primitives, $filter)) {
          return false;
        }
      }

      return true;
    });

    $results = array_values($filtered);

    // Apply sorting
    if ($criteria->hasOrders()) {
      usort($results, function (Entity $a, Entity $b) use ($criteria): int {
        $aData = $a->toPrimitives();
        $bData = $b->toPrimitives();

        foreach ($criteria->orders() as $order) {
          $field = $order->field;
          $direction = $order->direction;

          $aVal = $aData[$field] ?? null;
          $bVal = $bData[$field] ?? null;

          if ($aVal === $bVal) {
            continue;
          }

          $cmp = $aVal <=> $bVal;
          return $direction === Direction::DESC ? -$cmp : $cmp;
        }

        return 0;
      });
    }

    // Apply offset and limit
    $offset = $criteria->offset() ?? 0;
    $limit = $criteria->limit();

    if ($offset > 0 || $limit !== null) {
      $results = array_slice($results, $offset, $limit);
    }

    return $results;
  }

  public function count(?UnitOfWork $uow = null, bool $includeDeleted = false): int
  {
    return count($this->getAll($uow, $includeDeleted));
  }

  public function countBy(Criteria $criteria, ?UnitOfWork $uow = null, bool $includeDeleted = false): int
  {
    return count($this->search($criteria, $uow, $includeDeleted));
  }

  public function exists(Criteria $criteria, ?UnitOfWork $uow = null, bool $includeDeleted = false): bool
  {
    return $this->countBy($criteria, $uow, $includeDeleted) > 0;
  }

  public function create(Entity $entity, ?UnitOfWork $uow = null): Entity
  {
    $id = (string) $entity->getId();
    $this->entities[$id] = $entity;
    unset($this->softDeletedAt[$id]);

    return $entity;
  }

  public function update(Entity $entity, ?UnitOfWork $uow = null): Entity
  {
    return $this->create($entity, $uow);
  }

  public function save(Entity $entity, ?UnitOfWork $uow = null): Entity
  {
    return $this->create($entity, $uow);
  }

  public function delete(Entity $entity, ?UnitOfWork $uow = null): Entity
  {
    $id = (string) $entity->getId();

    if ($this->deleteMode === DeleteMode::SOFT) {
      $this->softDeletedAt[$id] = (new DateTimeImmutable())->format(DateTimeImmutable::ATOM);
    } else {
      unset($this->entities[$id], $this->softDeletedAt[$id]);
    }

    return $entity;
  }

  public function deleteByID(mixed $id, ?UnitOfWork $uow = null): void
  {
    $key = (string) $id;

    if ($this->deleteMode === DeleteMode::SOFT) {
      $this->softDeletedAt[$key] = (new DateTimeImmutable())->format(DateTimeImmutable::ATOM);
    } else {
      unset($this->entities[$key], $this->softDeletedAt[$key]);
    }
  }

  public function saveMany(array $entities, ?UnitOfWork $uow = null): array
  {
    $saved = [];
    foreach ($entities as $entity) {
      $saved[] = $this->save($entity, $uow);
    }
    return $saved;
  }

  public function restore(Entity $entity, ?UnitOfWork $uow = null): Entity
  {
    $id = (string) $entity->getId();

    if (!isset($this->softDeletedAt[$id])) {
      throw new EntityNotRecoverable("Entity with ID '{$id}' is not soft-deleted.");
    }

    unset($this->softDeletedAt[$id]);
    return $entity;
  }

  /**
   * @param array<string, mixed> $data
   */
  private function matchesFilter(array $data, Filter $filter): bool
  {
    $field = $filter->field;
    $op = $filter->operator;
    $filterVal = $filter->value;
    $actualVal = $data[$field] ?? null;

    return match ($op) {
      Operator::EQUAL => $actualVal == $filterVal,
      Operator::NOT_EQUAL => $actualVal != $filterVal,
      Operator::GREATER_THAN => $actualVal > $filterVal,
      Operator::GREATER_THAN_OR_EQUAL => $actualVal >= $filterVal,
      Operator::LESS_THAN => $actualVal < $filterVal,
      Operator::LESS_THAN_OR_EQUAL => $actualVal <= $filterVal,
      Operator::IN => is_array($filterVal) && in_array($actualVal, $filterVal, false),
      Operator::NOT_IN => is_array($filterVal) && !in_array($actualVal, $filterVal, false),
      Operator::LIKE => is_string($actualVal) && is_string($filterVal) && str_contains(
        strtolower($actualVal),
        strtolower(trim($filterVal, '%'))
      ),
      Operator::BETWEEN => is_array($filterVal) && count($filterVal) >= 2 &&
        $actualVal >= $filterVal[0] && $actualVal <= $filterVal[1],
      default => true,
    };
  }
}
