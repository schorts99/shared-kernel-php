<?php

namespace Schorts\SharedKernel\ValueObjects;

use Schorts\SharedKernel\ValueObjects\ValueObject;

abstract class URLValue implements ValueObject
{
  protected string $valueType = 'URL';
  protected string $value;
  protected array $allowedHosts;

  public function __construct(string $value, array $allowedHosts = [])
  {
    $this->value = $value;
    $this->allowedHosts = $allowedHosts;
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
    $host = parse_url($this->value, PHP_URL_HOST);

    if ($host === null) {
      return false;
    }

    return empty($this->allowedHosts) || in_array($host, $this->allowedHosts, true);
  }

  public function equals(mixed $other): bool
  {
    if (!$other instanceof URLValue) {
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
