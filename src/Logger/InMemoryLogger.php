<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\Logger;

use Throwable;

class InMemoryLogger extends Logger
{
  /** @var array<array{level: string, message: string, context: array, error: ?Throwable}> */
  private array $logs = [];

  public function log(string $message, array $context = []): void
  {
    $this->info($message, $context);
  }

  public function info(string $message, array $context = []): void
  {
    $this->logs[] = [
      'level' => 'info',
      'message' => $message,
      'context' => $context,
      'error' => null,
    ];
  }

  public function debug(string $message, array $context = []): void
  {
    $this->logs[] = [
      'level' => 'debug',
      'message' => $message,
      'context' => $context,
      'error' => null,
    ];
  }

  public function warn(string $message, array $context = []): void
  {
    $this->logs[] = [
      'level' => 'warn',
      'message' => $message,
      'context' => $context,
      'error' => null,
    ];
  }

  public function error(string $message, array $context = [], ?Throwable $error = null): void
  {
    $this->logs[] = [
      'level' => 'error',
      'message' => $message,
      'context' => $context,
      'error' => $error,
    ];
  }

  /**
   * @return array<array{level: string, message: string, context: array, error: ?Throwable}>
   */
  public function getLogs(?string $level = null): array
  {
    if ($level === null) {
      return $this->logs;
    }

    return array_values(array_filter(
      $this->logs,
      static fn (array $log): bool => $log['level'] === $level
    ));
  }

  public function clear(): void
  {
    $this->logs = [];
  }
}
