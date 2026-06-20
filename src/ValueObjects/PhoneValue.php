<?php

namespace Schorts\SharedKernel\ValueObjects;

use Schorts\SharedKernel\ValueObjects\ValueObject;

abstract class PhoneValue implements ValueObject
{
  protected string $valueType = 'Phone';
  protected string $value;
  private const REGEX = '/^\+(9[976]\d|8[987530]\d|6[987]\d|5[90]\d|42\d|3[875]\d|2[98654321]\d|9[8543210]|8[6421]|6[6543210]|5[87654321]|4[987654310]|3[9643210]|2[70]|7|1)\d{10,12}$/';

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

  public function getCountryCode(): ?string
  {
    if ($this->isValid()) {
      $countryCodeLength = strlen($this->value) - 10;

      return substr($this->value, 0, $countryCodeLength);
    }

    return null;
  }

  public function getPhoneNumber(): ?string
  {
    if ($this->isValid()) {
      return substr($this->value, -10);
    }

    return null;
  }

  public function getFormattedPhone(): ?string
  {
    if ($this->isValid()) {
      $phoneNumber = preg_replace('/(\d{3})(\d{3})(\d{4})/', '($1) $2-$3', $this->getPhoneNumber());

      return $this->getCountryCode() . ' ' . $phoneNumber;
    }

    return null;
  }

  public function equals(mixed $other): bool
  {
    if (!$other instanceof PhoneValue) {
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
