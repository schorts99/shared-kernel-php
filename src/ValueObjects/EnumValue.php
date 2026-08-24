<?php

namespace Schorts\SharedKernel\ValueObjects;

use Schorts\SharedKernel\ValueObjects\ValueObject;

abstract class EnumValue implements ValueObject
{
  protected string $valueType = 'Enum';
  protected array $allowedValues;
  protected string|null $value;

  public function __construct(array $allowedValues, string|null $value)
  {
    $this->allowedValues = $allowedValues;
    $this->value = $value;
  }

  public function getValue(): string|null
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
    return in_array($this->value, $this->allowedValues, true);
  }

  public function equals(mixed $other): bool
  {
    return $other instanceof EnumValue
      && $this->isValid()
      && $other->isValid()
      && $this->value === $other->getValue();
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
