<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\ValueObjects;

use JsonSerializable;
use Stringable;

interface ValueObject extends JsonSerializable, Stringable
{
  public function getValue(): mixed;
  public function getValueType(): string;
  public function getAttributeName(): string;
  public function isValid(): bool;
  public function equals(mixed $other): bool;
  public function __toString(): string;
  public function jsonSerialize(): mixed;
}
