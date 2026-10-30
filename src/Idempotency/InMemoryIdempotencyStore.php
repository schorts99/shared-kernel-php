<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\Idempotency;

class InMemoryIdempotencyStore implements IdempotencyStore
{
  /** @var array<string, array{result: mixed, expiresAt: ?int}> */
  private array $store = [];

  public function isProcessed(string $key): bool
  {
    if (!isset($this->store[$key])) {
      return false;
    }

    $entry = $this->store[$key];
    if ($entry['expiresAt'] !== null && time() > $entry['expiresAt']) {
      unset($this->store[$key]);
      return false;
    }

    return true;
  }

  public function markProcessed(string $key, mixed $result = null, ?int $ttl = null): void
  {
    $expiresAt = $ttl !== null ? time() + $ttl : null;
    $this->store[$key] = [
      'result' => $result,
      'expiresAt' => $expiresAt,
    ];
  }

  public function getResult(string $key): mixed
  {
    if (!$this->isProcessed($key)) {
      return null;
    }

    return $this->store[$key]['result'];
  }

  public function clear(string $key): bool
  {
    if (!isset($this->store[$key])) {
      return false;
    }

    unset($this->store[$key]);
    return true;
  }

  public function clearStore(): void
  {
    $this->store = [];
  }
}
