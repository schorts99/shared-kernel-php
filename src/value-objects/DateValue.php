<?php

namespace Schorts\SharedKernel\ValueObjects;

use DateTime;
use Schorts\SharedKernel\ValueObjects\ValueObject;

abstract class DateValue implements ValueObject
{
  protected string $valueType = 'Date';
  protected ?DateTime $value;
  protected ?DateTime $beforeDate;
  protected ?DateTime $afterDate;
  private bool $optional;

  public function __construct(?DateTime $value, ?DateTime $beforeDate = null, ?DateTime $afterDate = null, bool $optional = false)
  {
    $this->value = $value;
    $this->beforeDate = $beforeDate;
    $this->afterDate = $afterDate;
    $this->optional = $optional;
  }

  public function getValue(): ?DateTime
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
    if ($this->value === null) {
      return $this->optional;
    }

    if ($this->beforeDate !== null && $this->value > $this->beforeDate) {
      return false;
    }

    if ($this->afterDate !== null && $this->value < $this->afterDate) {
      return false;
    }

    return true;
  }

  public function equals(mixed $other): bool
  {
    if (!$other instanceof DateValue) {
      return false;
    }

    if (!$this->isValid() || !$other->isValid()) {
      return false;
    }

    return $this->value?->getTimestamp() === $other->getValue()?->getTimestamp();
  }

  public function __toString(): string
  {
    return $this->value?->format(DateTime::ATOM) ?? '';
  }

  public function jsonSerialize(): mixed
  {
    return $this->value?->format(DateTime::ATOM);
  }
}
