<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\ValueObjects;

abstract class PathValue implements ValueObject
{
  protected string $valueType = 'Path';
  protected ?string $value;
  /** @var array<string> */
  protected array $allowedRoots;

  public function __construct(?string $value, array $allowedRoots = [])
  {
    $this->value = $value;
    $this->allowedRoots = $allowedRoots;
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

  /**
   * @return array{path: string, escapedRoot: bool}
   */
  private function resolveNormalizedPath(string $rawPath): array
  {
    $posix = str_replace('\\', '/', $rawPath);
    $isAbsolute = str_starts_with($posix, '/');
    $parts = explode('/', $posix);
    $resolved = [];
    $escapedRoot = false;

    foreach ($parts as $part) {
      if ($part === '' || $part === '.') {
        continue;
      }
      if ($part === '..') {
        if (!empty($resolved) && end($resolved) !== '..') {
          array_pop($resolved);
        } else {
          $escapedRoot = true;
          if (!$isAbsolute) {
            $resolved[] = '..';
          }
        }
      } else {
        $resolved[] = $part;
      }
    }

    $joined = ($isAbsolute ? '/' : '') . implode('/', $resolved);
    return [
      'path' => $joined !== '' ? $joined : ($isAbsolute ? '/' : '.'),
      'escapedRoot' => $escapedRoot,
    ];
  }

  public function isValid(): bool
  {
    if ($this->value === null || trim($this->value) === '') {
      return false;
    }

    if (str_contains($this->value, "\0")) {
      return false;
    }

    $resolution = $this->resolveNormalizedPath($this->value);
    if ($resolution['escapedRoot']) {
      return false;
    }

    $normalized = $resolution['path'];

    if (!empty($this->allowedRoots)) {
      foreach ($this->allowedRoots as $root) {
        $rootRes = $this->resolveNormalizedPath($root);
        $normalizedRoot = $rootRes['path'];

        if ($normalizedRoot === '/' && str_starts_with($normalized, '/')) {
          return true;
        }

        if ($normalized === $normalizedRoot || str_starts_with($normalized, $normalizedRoot . '/')) {
          return true;
        }
      }
      return false;
    }

    return true;
  }

  public function equals(mixed $other): bool
  {
    if (!$other instanceof PathValue) {
      return false;
    }

    if (!$this->isValid() || !$other->isValid()) {
      return false;
    }

    return $this->resolveNormalizedPath($this->value ?? '')['path'] ===
      $this->resolveNormalizedPath($other->getValue() ?? '')['path'];
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

  public static function fromPrimitives(string $value, array $allowedRoots = []): static
  {
    return new static($value, $allowedRoots);
  }
}
