<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\Result;

use JsonSerializable;
use RuntimeException;
use Throwable;

final class Result implements JsonSerializable
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

  public static function success(mixed $value = null): self
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

  /**
   * Safely execute a callable that might throw an exception, returning a Result.
   */
  public static function fromCallable(callable $fn, ?callable $mapError = null): self
  {
    try {
      $val = $fn();
      return self::success($val);
    } catch (Throwable $e) {
      if ($mapError !== null) {
        return self::failure($mapError($e));
      }
      return self::failure($e);
    }
  }

  /**
   * @param array<Result> $results
   */
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

      throw new RuntimeException('Result failure: ' . (string) $this->error);
    }

    return $this->value;
  }

  public function getError(): mixed
  {
    if ($this->isSuccess()) {
      throw new RuntimeException('Cannot get error from a success result');
    }

    return $this->error;
  }

  public function unwrap(): mixed
  {
    return $this->getValue();
  }

  public function unwrapOr(mixed $defaultValue): mixed
  {
    return $this->getOrElse($defaultValue);
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

  /**
   * @return array{isSuccess: bool, value?: mixed, error?: mixed}
   */
  public function toPrimitives(): array
  {
    if ($this->isSuccess()) {
      $val = $this->value;
      if (is_object($val) && method_exists($val, 'toPrimitives')) {
        $val = $val->toPrimitives();
      }

      return [
        'isSuccess' => true,
        'value' => $val,
      ];
    }

    $err = $this->error;
    if (is_object($err) && method_exists($err, 'toPrimitives')) {
      $err = $err->toPrimitives();
    } elseif ($err instanceof Throwable) {
      $err = [
        'name' => get_class($err),
        'message' => $err->getMessage(),
        'code' => $err->getCode(),
      ];
    }

    return [
      'isSuccess' => false,
      'error' => $err,
    ];
  }

  /**
   * @param array{isSuccess: bool, value?: mixed, error?: mixed} $primitives
   */
  public static function fromPrimitives(
    array $primitives,
    ?callable $hydrateValue = null,
    ?callable $hydrateError = null,
  ): self {
    $isSuccess = (bool) ($primitives['isSuccess'] ?? false);

    if ($isSuccess) {
      $rawVal = $primitives['value'] ?? null;
      $val = $hydrateValue !== null ? $hydrateValue($rawVal) : $rawVal;
      return self::success($val);
    }

    $rawErr = $primitives['error'] ?? null;
    if ($hydrateError !== null) {
      return self::failure($hydrateError($rawErr));
    }

    if (is_array($rawErr) && isset($rawErr['message'])) {
      $msg = (string) $rawErr['message'];
      $code = isset($rawErr['code']) && is_int($rawErr['code']) ? $rawErr['code'] : 0;
      return self::failure(new RuntimeException($msg, $code));
    }

    return self::failure($rawErr);
  }

  public function jsonSerialize(): mixed
  {
    return $this->toPrimitives();
  }
}
