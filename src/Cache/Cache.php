<?php

namespace Schorts\SharedKernel\Cache;

interface Cache
{
  public function get(string $key): mixed;
  public function set(string $key, mixed $value, ?int $ttl = null, ?array $tags = null): void;
  public function delete(string $key): bool;
  public function deleteByTag(string $tag): void;
  public function deleteByTags(array $tags): void;
  public function clear(): void;
  public function has(string $key): bool;
}
