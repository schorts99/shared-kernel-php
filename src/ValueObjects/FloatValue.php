<?php

namespace Schorts\SharedKernel\ValueObjects;

use Schorts\SharedKernel\ValueObjects\ValueObject;

abstract class FloatValue implements ValueObject
{
  protected string $valueType = 'Float';
  protected float $value;
  protected ?int $decimals;
  protected ?float $min;
  protected ?float $max;

  public function __construct(float $value, ?int $decimals = null, ?float $min = null, ?float $max = null)
  {
    $this->decimals = $decimals;
    $this->min = $min;
    $this->max = $max;
    $this->value = $this->transform($value);
  }

  public function getValue(): float
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
    return !is_nan($this->value)
      && ($this->min !== null ? $this->value >= $this->min : true)
      && ($this->max !== null ? $this->value <= $this->max : true);
  }

  public function equals(mixed $other): bool
  {
    if (!$other instanceof FloatValue) {
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

  private function transform(float $value): float
  {
    if ($this->decimals === null) {
      return $value;
    }

    $factor = pow(10, $this->decimals);

    return round($value * $factor) / $factor;
  }
}
