<?php

namespace Schorts\SharedKernel\ValueObjects;

use Schorts\SharedKernel\ValueObjects\ValueObject;

abstract class UUIDValue implements ValueObject
{
  protected string $valueType = 'UUID';
  protected ?string $value;
  private bool $optional;
  private const REGEX = '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

  public function __construct(string|null $value, bool $optional = false)
  {
    $this->value = $value;
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
    if ($this->optional) {
      return $this->value === null || preg_match(self::REGEX, $this->value) === 1;
    }

    return $this->value !== null && preg_match(self::REGEX, $this->value) === 1;
  }

  public function equals(mixed $other): bool
  {
    if (!$other instanceof UUIDValue) {
      return false;
    }

    if (!$this->isValid() || !$other->isValid()) {
      return false;
    }

    return $this->value === $other->getValue();
  }

  public function __toString(): string
  {
    return $this->value ?? '';
  }

  public function jsonSerialize(): mixed
  {
    return $this->value;
  }
}
