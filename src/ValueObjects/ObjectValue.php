<?php

namespace Schorts\SharedKernel\ValueObjects;

use Schorts\SharedKernel\ValueObjects\ValueObject;

abstract class ObjectValue implements ValueObject
{
  protected string $valueType = 'Object';
  protected ?array $value;
  protected array $schema;
  protected bool $optional;

  public function __construct(?array $value, array $schema, bool $optional = false)
  {
    $this->optional = $optional;
    $this->schema   = $schema;

    if ($optional && $value === null) {
      $this->value = null;
    } elseif ($value !== null && is_array($value) && !array_is_list($value)) {
      $this->value = $this->deepFreeze($value);
    } else {
      $this->value = $value;
    }
  }

  public function getValue(): ?array
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
    if ($this->optional && $this->value === null) {
      return true;
    }

    if ($this->value === null) {
      return false;
    }

    if (!is_array($this->value) || array_is_list($this->value)) {
      return false;
    }

    return $this->validateObject($this->value, $this->schema);
  }

  public function equals(mixed $other): bool
  {
    if (!$other instanceof self) {
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

  private function validateRule(mixed $value, array $rule): bool
  {
    if (array_key_exists('required', $rule)) {
      return $value !== null;
    }

    if (isset($rule['greaterThan'])) {
      return is_numeric($value) && $value > $rule['greaterThan'];
    }

    if (isset($rule['greaterThanOrEqual'])) {
      return is_numeric($value) && $value >= $rule['greaterThanOrEqual'];
    }

    if (isset($rule['lessThan'])) {
      return is_numeric($value) && $value < $rule['lessThan'];
    }

    if (isset($rule['lessThanOrEqual'])) {
      return is_numeric($value) && $value <= $rule['lessThanOrEqual'];
    }

    if (isset($rule['type'])) {
      return gettype($value) === $rule['type'];
    }

    if (isset($rule['enum'])) {
      return in_array($value, $rule['enum'], true);
    }

    if (isset($rule['regex'])) {
      return is_string($value) && preg_match($rule['regex'], $value) === 1;
    }

    if (isset($rule['minLength'])) {
      return is_string($value) && mb_strlen($value) >= $rule['minLength'];
    }

    if (isset($rule['maxLength'])) {
      return is_string($value) && mb_strlen($value) <= $rule['maxLength'];
    }

    if (isset($rule['custom']) && is_callable($rule['custom'])) {
      return (bool) ($rule['custom'])($value);
    }

    return true;
  }

  private function validateObject(array $obj, array $schema): bool
  {
    foreach ($schema as $key => $rulesOrNested) {
      $value = $obj[$key] ?? null;

      if (is_array($rulesOrNested) && array_key_exists('_', $rulesOrNested)) {
        $itemSchema = $rulesOrNested['_'];
        $isRequired = $this->schemaHasRequired($itemSchema);

        if (!$isRequired && $value === null) {
          continue;
        }

        if (!is_array($value)) {
          return false;
        }

        if ($this->isRuleArray($itemSchema)) {
          foreach ($value as $item) {
            foreach ($itemSchema as $rule) {
              if (!$this->validateRule($item, $rule)) {
                return false;
              }
            }
          }
        } else {
          foreach ($value as $item) {
            if (!is_array($item) || array_is_list($item)) {
              return false;
            }

            if (!$this->validateObject($item, $itemSchema)) {
              return false;
            }
          }
        }

        continue;
      }

      if ($this->isRuleArray($rulesOrNested)) {
        $isRequired = $this->schemaHasRequired($rulesOrNested);

        if (!$isRequired && $value === null) {
          continue;
        }

        foreach ($rulesOrNested as $rule) {
          if (!$this->validateRule($value, $rule)) {
            return false;
          }
        }

        continue;
      }

      if (is_array($rulesOrNested)) {
        if ($value === null) {
          continue;
        }

        if (!is_array($value) || array_is_list($value)) {
          return false;
        }

        if (!$this->validateObject($value, $rulesOrNested)) {
          return false;
        }

        continue;
      }

      return false;
    }

    return true;
  }

  private function schemaHasRequired(array $schemaOrRules): bool
  {
    if ($this->isRuleArray($schemaOrRules)) {
      foreach ($schemaOrRules as $rule) {
        if (array_key_exists('required', $rule)) {
          return true;
        }
      }
    }

    return false;
  }

  private function isRuleArray(array $arr): bool
  {
    return isset($arr[0]) && is_array($arr[0]);
  }

  private function deepFreeze(array $value): array
  {
    $result = [];

    foreach ($value as $key => $item) {
      $result[$key] = is_array($item) ? $this->deepFreeze($item) : $item;
    }

    return $result;
  }
}
