<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\Cache;

class InMemoryCache implements Cache
{
  /** @var array<string, array{value: mixed, expiresAt: ?int, tags: array<string>}> */
  private array $storage = [];

  public function get(string $key): mixed
  {
    if (!$this->has($key)) {
      return null;
    }

    return $this->storage[$key]['value'];
  }

  public function set(string $key, mixed $value, ?int $ttl = null, ?array $tags = null): void
  {
    $expiresAt = $ttl !== null ? time() + $ttl : null;

    $this->storage[$key] = [
      'value' => $value,
      'expiresAt' => $expiresAt,
      'tags' => $tags ?? [],
    ];
  }

  public function delete(string $key): bool
  {
    if (!isset($this->storage[$key])) {
      return false;
    }

    unset($this->storage[$key]);
    return true;
  }

  public function deleteByTag(string $tag): void
  {
    foreach ($this->storage as $key => $entry) {
      if (in_array($tag, $entry['tags'], true)) {
        unset($this->storage[$key]);
      }
    }
  }

  public function deleteByTags(array $tags): void
  {
    foreach ($tags as $tag) {
      $this->deleteByTag($tag);
    }
  }

  public function clear(): void
  {
    $this->storage = [];
  }

  public function has(string $key): bool
  {
    if (!isset($this->storage[$key])) {
      return false;
    }

    $entry = $this->storage[$key];
    if ($entry['expiresAt'] !== null && time() > $entry['expiresAt']) {
      unset($this->storage[$key]);
      return false;
    }

    return true;
  }
}
