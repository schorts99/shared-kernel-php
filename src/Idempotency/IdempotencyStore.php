<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\Idempotency;

interface IdempotencyStore
{
  public function isProcessed(string $key): bool;

  public function markProcessed(string $key, mixed $result = null, ?int $ttl = null): void;

  public function getResult(string $key): mixed;

  public function clear(string $key): bool;

  public function clearStore(): void;
}
