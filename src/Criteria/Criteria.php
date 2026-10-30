<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\Criteria;

use JsonSerializable;
use Schorts\SharedKernel\Criteria\Exceptions\LimitNotValid;
use Schorts\SharedKernel\Criteria\Exceptions\OffsetNotValid;
use Schorts\SharedKernel\Criteria\Exceptions\OperatorNotValid;

class Criteria implements JsonSerializable
{
  /** @var array<Filter> */
  private array $filters;
  /** @var array<Order> */
  private array $orders;
  private ?int $limit;
  private ?int $offset;

  /**
   * @return array<Filter>
   */
  public function filters(): array { return $this->filters; }

  /**
   * @return array<Order>
   */
  public function orders(): array { return $this->orders; }

  public function limit(): ?int { return $this->limit; }

  public function offset(): ?int { return $this->offset; }

  /**
   * @param array<Filter> $filters
   * @param array<Order> $orders
   */
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

  public function where(string $field, Operator|string $operator, mixed $value): self
  {
    $op = is_string($operator) ? (Operator::tryFrom(strtoupper($operator)) ?? throw new OperatorNotValid($operator)) : $operator;
    $filters = [...$this->filters, new Filter($field, $op, $value)];

    return new self($filters, $this->orders, $this->limit, $this->offset);
  }

  public function whereEqual(string $field, mixed $value): self
  {
    return $this->where($field, Operator::EQUAL, $value);
  }

  public function whereNotEqual(string $field, mixed $value): self
  {
    return $this->where($field, Operator::NOT_EQUAL, $value);
  }

  public function whereIn(string $field, array $values): self
  {
    return $this->where($field, Operator::IN, $values);
  }

  public function whereNotIn(string $field, array $values): self
  {
    return $this->where($field, Operator::NOT_IN, $values);
  }

  public function whereGreaterThan(string $field, mixed $value): self
  {
    return $this->where($field, Operator::GREATER_THAN, $value);
  }

  public function whereGreaterThanOrEqual(string $field, mixed $value): self
  {
    return $this->where($field, Operator::GREATER_THAN_OR_EQUAL, $value);
  }

  public function whereLessThan(string $field, mixed $value): self
  {
    return $this->where($field, Operator::LESS_THAN, $value);
  }

  public function whereLessThanOrEqual(string $field, mixed $value): self
  {
    return $this->where($field, Operator::LESS_THAN_OR_EQUAL, $value);
  }

  public function whereLike(string $field, string $value): self
  {
    return $this->where($field, Operator::LIKE, $value);
  }

  public function whereBetween(string $field, mixed $from, mixed $to): self
  {
    return $this->where($field, Operator::BETWEEN, [$from, $to]);
  }

  public function orderBy(string $field, Direction|string $direction = Direction::ASC): self
  {
    $dir = is_string($direction) ? Direction::from(strtoupper($direction)) : $direction;
    $orders = [...$this->orders, new Order($field, $dir)];

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
    if ($offset < 0) {
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

  /**
   * @return array<string, mixed>
   */
  public function toArray(): array
  {
    return [
      'filters' => array_map(static fn (Filter $f) => [
        'field' => $f->field,
        'operator' => $f->operator->value,
        'value' => $f->value,
      ], $this->filters),
      'orders' => array_map(static fn (Order $o) => [
        'field' => $o->field,
        'direction' => $o->direction->value,
      ], $this->orders),
      'limit' => $this->limit,
      'offset' => $this->offset,
    ];
  }

  public function jsonSerialize(): mixed
  {
    return $this->toArray();
  }
}
