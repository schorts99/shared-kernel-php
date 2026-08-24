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

  private function validateRule(mixed $value, array $rule): bool
  {
    if (array_key_exists('required', $rule)) {
      if ($rule['required'] && $value === null) {
        return false;
      }
    }

    if (isset($rule['greater_than'])) {
      if (!is_numeric($value) || $value <= $rule['greater_than']) {
        return false;
      }
    }

    if (isset($rule['greater_than_or_equal'])) {
      if (!is_numeric($value) || $value < $rule['greater_than_or_equal']) {
        return false;
      }
    }

    if (isset($rule['less_than'])) {
      if (!is_numeric($value) || $value >= $rule['less_than']) {
        return false;
      }
    }

    if (isset($rule['less_than_or_equal'])) {
      if (!is_numeric($value) || $value > $rule['less_than_or_equal']) {
        return false;
      }
    }

    if (isset($rule['type'])) {
      if (gettype($value) !== $rule['type']) {
        return false;
      }
    }

    if (isset($rule['enum'])) {
      if (!in_array($value, $rule['enum'], true)) {
        return false;
      }
    }

    if (isset($rule['custom']) && is_callable($rule['custom'])) {
      if (!(bool) call_user_func($rule['custom'], $value)) {
        return false;
      }
    }

    return true;
  }

  private function validateObject(array $obj, array $schema): bool
  {
    foreach ($schema as $key => $rulesOrNested) {
      $value = $obj[$key] ?? null;

      if (is_array($rulesOrNested) && array_key_exists('_', $rulesOrNested)) {
        if (isset($rulesOrNested['required']) && $rulesOrNested['required'] && $value === null) {
          return false;
        }

        if (isset($rulesOrNested['type']) && gettype($value) !== $rulesOrNested['type']) {
          return false;
        }

        if (!is_array($value)) {
          return false;
        }

        foreach ($value as $item) {
          foreach ($rulesOrNested['_'] as $rule) {
            if (!$this->validateRule($item, $rule)) {
              return false;
            }
          }
        }

        continue;
      }

      if ($this->isRuleArray($rulesOrNested)) {
        foreach ($rulesOrNested as $rule) {
          if (!$this->validateRule($value, $rule)) {
            return false;
          }
        }

        continue;
      }

      if (is_array($rulesOrNested) && is_array($value)) {
        if (!$this->validateObject($value, $rulesOrNested)) {
          return false;
        }

        continue;
      }

      return false;
    }

    return true;
  }

  public function isValid(): bool
  {
    if (!is_array($this->value)) {
      return false;
    }

    foreach ($this->value as $item) {
      if ($this->isPrimitive()) {
        foreach ($this->schema as $rule) {
          if (!$this->validateRule($item, $rule)) {
            return false;
          }
        }
      } else {
        if (!is_array($item) || !$this->validateObject($item, $this->schema)) {
          return false;
        }
      }
    }

    return true;
  }

  public function equals(mixed $other): bool
  {
    if (!$other instanceof ArrayValue) {
      return false;
    }

    if (!$this->isValid() || !$other->isValid()) {
      return false;
    }

    return $this->value == $other->getValue();
  }  

  private function isRuleArray(array $arr): bool
  {
    return isset($arr[0]) && is_array($arr[0]);
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
