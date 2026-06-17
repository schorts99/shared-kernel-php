<?php

namespace Schorts\SharedKernel\Criteria;

use Schorts\SharedKernel\Criteria\Operator;
use Schorts\SharedKernel\Criteria\Direction;
use Schorts\SharedKernel\Criteria\Order;
use Schorts\SharedKernel\Criteria\Filter;
use Schorts\SharedKernel\Criteria\Exceptions\LimitNotValid;
use Schorts\SharedKernel\Criteria\Exceptions\OffsetNotValid;

class Criteria
{
  private array $filters;
  private array $orders;
  private ?int $limit;
  private ?int $offset;

  public function filters(): array { return $this->filters; }
  public function orders(): array { return $this->orders; }
  public function limit(): ?int { return $this->limit; }
  public function offset(): ?int { return $this->offset; }

  public function __construct(
    array $filters = [],
    array $orders = [],
    ?int $limit = null,
    ?int $offset = null
  ) {
    $this->filters = $filters;
    $this->orders = $orders;
    $this->limit = $limit;
    $this->offset = $offset;
  }

  public function where(string $field, Operator $operator, mixed $value): self
  {
    $filters = [...$this->filters, new Filter($field, $operator, $value)];

    return new self($filters, $this->orders, $this->limit, $this->offset);
  }

  public function orderBy(string $field, Direction $direction = Direction::ASC): self
  {
    $orders = [...$this->orders, new Order($field, $direction)];

    return new self($this->filters, $orders, $this->limit, $this->offset);
  }

  public function limitResults(int $limit): self
  {
    if ($limit < 1) {
      throw new LimitNotValid($limit);
    }

    return new self($this->filters, $this->orders, $limit, $this->offset);
  }

  public function offsetResults(int $offset): self
  {
    if ($offset < 1) {
      throw new OffsetNotValid($offset);
    }

    return new self($this->filters, $this->orders, $this->limit, $offset);
  }

  public function hasFilters(): bool
  {
    return count($this->filters) > 0;
  }

  public function hasOrders(): bool
  {
    return count($this->orders) > 0;
  }

  public static function none(): self
  {
    return new self();
  }
}
