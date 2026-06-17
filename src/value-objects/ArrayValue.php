<?php

namespace Schorts\SharedKernel\ValueObjects;

use Schorts\SharedKernel\ValueObjects\ValueObject;

abstract class ArrayValue implements ValueObject
{
  protected string $valueType = 'Array';
  protected array $value;
  protected array $schema;

  public function __construct(array $value, array $schema)
  {
    $this->value = $value;
    $this->schema = $schema;
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
  abstract public function isPrimitive(): bool;

  public function isValid(): bool
  {
    foreach ($this->value as $item) {
      if ($this->isPrimitive()) {
        foreach ($this->schema as $rule) {
          if (!$this->validateRule($item, $rule)) {
            return false;
          }
        }
      } else {
        if (!$this->validateObject($item, $this->schema)) {
          return false;
        }
      }
    }

    return true;
  }

  private function validateObject(array $obj, array $schema): bool
  {
    foreach ($schema as $key => $rulesOrNested) {
      $value = $obj[$key] ?? null;

      if (is_array($rulesOrNested) && array_key_exists('_', $rulesOrNested) && is_array($value)) {
        foreach ($value as $item) {
          foreach ($rulesOrNested['_'] as $rule) {
            if (!$this->validateRule($item, $rule)) {
              return false;
            }
          }
        }
      } elseif (is_array($rulesOrNested) && $this->isRuleArray($rulesOrNested)) {
        foreach ($rulesOrNested as $rule) {
          if (!$this->validateRule($value, $rule)) {
            return false;
          }
        }
      } elseif (is_array($rulesOrNested) && $value !== null && is_array($value)) {
        if (!$this->validateObject($value, $rulesOrNested)) {
          return false;
        }
      }
    }

    return true;
  }

  private function validateRule(mixed $value, array $rule): bool
  {
    if (isset($rule['required'])) {
      return $value !== null;
    }

    if (isset($rule['greater_than'])) {
      return is_numeric($value) && $value > $rule['greater_than'];
    }

    if (isset($rule['greater_than_or_equal'])) {
      return is_numeric($value) && $value >= $rule['greater_than_or_equal'];
    }

    if (isset($rule['less_than'])) {
      return is_numeric($value) && $value < $rule['less_than'];
    }

    if (isset($rule['less_than_or_equal'])) {
      return is_numeric($value) && $value <= $rule['less_than_or_equal'];
    }

    if (isset($rule['type'])) {
      return gettype($value) === $rule['type'];
    }

    if (isset($rule['enum'])) {
      return in_array($value, $rule['enum'], true);
    }

    if (isset($rule['custom']) && is_callable($rule['custom'])) {
      return (bool) call_user_func($rule['custom'], $value);
    }

    return true;
  }

  private function isRuleArray(array $arr): bool
  {
    return isset($arr[0]) && is_array($arr[0]);
  }

  public function equals(mixed $other): bool
  {
    if (!$other instanceof ArrayValue) {
      return false;
    }

    if (!$this->isValid() || !$other->isValid()) {
      return false;
    }

    return json_encode($this->value) === json_encode($other->getValue());
  }

  public function __toString(): string
  {
    return json_encode($this->value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
  }

  public function jsonSerialize(): mixed
  {
    return $this->value;
  }
}
