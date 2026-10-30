<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\ValueObjects;

abstract class HtmlValue implements ValueObject
{
  protected string $valueType = 'HTML';
  protected ?string $value;

  public function __construct(?string $value)
  {
    $this->value = $value;
  }

  abstract public function getAttributeName(): string;

  public function getValue(): ?string
  {
    return $this->value;
  }

  public function getValueType(): string
  {
    return $this->valueType;
  }

  public function isValid(): bool
  {
    if ($this->value === null) {
      return false;
    }

    return preg_match('/<\/?[a-z][\s\S]*>/i', $this->value) === 1;
  }

  public function equals(mixed $other): bool
  {
    if (!$other instanceof HtmlValue) {
      return false;
    }

    if (!$this->isValid() || !$other->isValid()) {
      return false;
    }

    return trim($this->value ?? '') === trim($other->getValue() ?? '');
  }

  public function __toString(): string
  {
    return (string) $this->value;
  }

  public function jsonSerialize(): mixed
  {
    return $this->value;
  }

  public function toPrimitives(): ?string
  {
    return $this->value;
  }

  public static function fromPrimitives(string $value): static
  {
    return new static($value);
  }
}
