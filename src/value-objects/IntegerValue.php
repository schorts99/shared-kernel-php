<?php

namespace Schorts\SharedKernel\ValueObjects;

use Schorts\SharedKernel\ValueObjects\ValueObject;

abstract class IntegerValue implements ValueObject
{
  protected string $valueType = 'Integer';
  protected ?int $min;
  protected ?int $max;
  protected int $value;

  public function __construct(int $value, ?int $min = null, ?int $max = null)
  {
    $this->min = $min;
    $this->max = $max;
    $this->value = $value;
  }

  public function getValue(): int
  {
    return $this->value;
  }

  public function getValueType(): string
  {
    return $this->valueType;
  }

  abstract public function getAttributeName(): string;

  public function isValid(): bool
  {
    return ($this->min !== null ? $this->value >= $this->min : true)
      && ($this->max !== null ? $this->value <= $this->max : true)
      && is_int($this->value);
  }

  public function equals(mixed $other): bool
  {
    if (!$other instanceof IntegerValue) {
      return false;
    }

    if (!$this->isValid() || !$other->isValid()) {
      return false;
    }

    return $this->value === $other->getValue();
  }

  public function __toString(): string
  {
    return (string) $this->value;
  }

  public function jsonSerialize(): mixed
  {
    return $this->value;
  }
}
