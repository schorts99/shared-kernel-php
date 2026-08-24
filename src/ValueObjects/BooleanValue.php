<?php

namespace Schorts\SharedKernel\ValueObjects;

use Schorts\SharedKernel\ValueObjects\ValueObject;

abstract class BooleanValue implements ValueObject
{
  protected string $valueType = 'Boolean';
  protected bool $value;

  public function __construct(bool $value)
  {
    $this->value = $value;
  }

  public function getValue(): bool
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
    return true;
  }

  public function equals(mixed $other): bool
  {
    if (!$other instanceof BooleanValue) {
      return false;
    }

    if (!$this->isValid() || !$other->isValid()) {
      return false;
    }

    return $this->value === $other->getValue();
  }

  public function __toString(): string
  {
    return $this->value ? 'true' : 'false';
  }

  public function jsonSerialize(): mixed
  {
    return $this->value;
  }
}
