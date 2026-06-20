<?php

namespace Schorts\SharedKernel\Result;

use Throwable;

final class Result
{
  private bool $isSuccess;
  private mixed $value;
  private mixed $error;

  private function __construct(bool $isSuccess, mixed $value = null, mixed $error = null)
  {
    $this->isSuccess = $isSuccess;
    $this->value = $value;
    $this->error = $error;
  }

  public static function success(mixed $value): self
  {
    return new self(true, $value, null);
  }

  public static function failure(mixed $error): self
  {
    return new self(false, null, $error);
  }

  public static function error(Throwable $error): self
  {
    return self::failure($error);
  }

  public static function combine(array $results): self
  {
    $values = [];

    foreach ($results as $result) {
      if ($result->isFailure()) {
        return self::failure($result->getError());
      }

      $values[] = $result->getValue();
    }

    return self::success($values);
  }

  public function isSuccess(): bool
  {
    return $this->isSuccess;
  }

  public function isFailure(): bool
  {
    return !$this->isSuccess;
  }

  public function getValue(): mixed
  {
    if ($this->isFailure()) {
      if ($this->error instanceof Throwable) {
        throw $this->error;
      }

      throw new \RuntimeException("Result failure: " . (string)$this->error);
    }

    return $this->value;
  }

  public function getError(): mixed
  {
    if ($this->isSuccess()) {
      throw new \RuntimeException("Cannot get error from a success result");
    }

    return $this->error;
  }

  public function map(callable $fn): self
  {
    return $this->isSuccess()
      ? self::success($fn($this->getValue()))
      : self::failure($this->getError());
  }

  public function flatMap(callable $fn): self
  {
    return $this->isSuccess()
      ? $fn($this->getValue())
      : self::failure($this->getError());
  }

  public function mapError(callable $fn): self
  {
    return $this->isFailure()
      ? self::failure($fn($this->getError()))
      : self::success($this->value);
  }

  public function getOrElse(mixed $defaultValue): mixed
  {
    return $this->isSuccess() ? $this->getValue() : $defaultValue;
  }

  public function onSuccess(callable $fn): self
  {
    if ($this->isSuccess()) {
      $fn($this->getValue());
    }

    return $this;
  }

  public function onFailure(callable $fn): self
  {
    if ($this->isFailure()) {
      $fn($this->getError());
    }

    return $this;
  }

  public function match(callable $onSuccess, callable $onFailure): mixed
  {
    return $this->isSuccess()
      ? $onSuccess($this->getValue())
      : $onFailure($this->getError());
  }
}
