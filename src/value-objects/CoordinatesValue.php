<?php

namespace Schorts\SharedKernel\ValueObjects;

use Schorts\SharedKernel\ValueObjects\ValueObject;

abstract class CoordinatesValue implements ValueObject
{
  protected string $valueType = 'Coordinates';
  protected array $value;
  private const EPSILON = 1e-6;

  public function __construct(array $value)
  {
    $this->value = $value;
  }

  public function getValue(): array
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
    $lat = $this->value['latitude'] ?? null;
    $lon = $this->value['longitude'] ?? null;
    $validLat = $lat !== null && $lat >= -90 && $lat <= 90;
    $validLon = $lon !== null && $lon >= -180 && $lon <= 180;

    return $validLat && $validLon;
  }

  public function getLatitude(): ?float
  {
    return $this->isValid() ? $this->value['latitude'] : null;
  }

  public function getLongitude(): ?float
  {
    return $this->isValid() ? $this->value['longitude'] : null;
  }

  public function equals(mixed $other): bool
  {
    if (!$other instanceof CoordinatesValue) {
      return false;
    }

    if (!$this->isValid() || !$other->isValid()) {
      return false;
    }

    $latDiff = abs($this->value['latitude'] - $other->getValue()['latitude']);
    $lonDiff = abs($this->value['longitude'] - $other->getValue()['longitude']);

    return $latDiff < self::EPSILON && $lonDiff < self::EPSILON;
  }

  public function __toString(): string
  {
    return sprintf('%f,%f', $this->value['latitude'], $this->value['longitude']);
  }

  public function jsonSerialize(): mixed
  {
    return $this->value;
  }
}
