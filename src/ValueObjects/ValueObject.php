<?php

namespace Schorts\SharedKernel\ValueObjects;

interface ValueObject
{
  public function getValue(): mixed;
  public function getValueType(): string;
  public function getAttributeName(): string;
  public function isValid(): bool;
  public function equals(mixed $other): bool;
  public function __toString(): string;
  public function jsonSerialize(): mixed;
}
