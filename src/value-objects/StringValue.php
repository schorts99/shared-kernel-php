<?php

namespace Schorts\SharedKernel\ValueObjects;

use Schorts\SharedKernel\ValueObjects\ValueObject;

abstract class StringValue implements ValueObject
{
  protected string $valueType = 'String';
  protected ?string $value;
  protected int $minLength;
  protected ?int $maxLength;
  protected bool $optional;

  public function __construct(?string $value, int $minLength = 0, ?int $maxLength = null, bool $optional = false)
  {
    $this->value = $value;
    $this->minLength = $minLength;
    $this->maxLength = $maxLength;
    $this->optional = $optional;
  }

  public function getValue(): ?string
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
    if ($this->optional && $this->value === null) {
      return true;
    }

    if ($this->value === null) {
      return false;
    }

    $length = strlen($this->value);

    return $length >= $this->minLength
      && ($this->maxLength !== null ? $length <= $this->maxLength : true);
  }

  public function equals(mixed $other): bool
  {
    if (!$other instanceof StringValue) {
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
