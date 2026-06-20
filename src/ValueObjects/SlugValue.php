<?php

namespace Schorts\SharedKernel\ValueObjects;

use Schorts\SharedKernel\ValueObjects\ValueObject;

abstract class SlugValue implements ValueObject
{
  protected string $valueType = 'Slug';
  protected string $value;
  private const REGEX = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

  public function __construct(string $value)
  {
    $this->value = $value;
  }

  public function getValue(): string
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
    return preg_match(self::REGEX, $this->value) === 1;
  }

  public function equals(mixed $other): bool
  {
    if (!$other instanceof SlugValue) {
      return false;
    }

    if (!$this->isValid() || !$other->isValid()) {
      return false;
    }

    return $this->value === $other->getValue();
  }

  public function __toString(): string
  {
    return $this->value;
  }

  public function jsonSerialize(): mixed
  {
    return $this->value;
  }
}
