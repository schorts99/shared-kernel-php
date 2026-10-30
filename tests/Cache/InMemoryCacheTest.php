<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\Tests\Cache;

use PHPUnit\Framework\TestCase;
use Schorts\SharedKernel\Cache\InMemoryCache;

final class InMemoryCacheTest extends TestCase
{
  public function testGetSetDelete(): void
  {
    $cache = new InMemoryCache();

    $this->assertFalse($cache->has('key1'));
    $this->assertNull($cache->get('key1'));

    $cache->set('key1', 'value1');
    $this->assertTrue($cache->has('key1'));
    $this->assertSame('value1', $cache->get('key1'));

    $this->assertTrue($cache->delete('key1'));
    $this->assertFalse($cache->has('key1'));
  }

  public function testTagInvalidation(): void
  {
    $cache = new InMemoryCache();

    $cache->set('user:1', ['id' => 1], tags: ['users']);
    $cache->set('user:2', ['id' => 2], tags: ['users']);
    $cache->set('post:1', ['id' => 1], tags: ['posts']);

    $this->assertTrue($cache->has('user:1'));
    $this->assertTrue($cache->has('user:2'));
    $this->assertTrue($cache->has('post:1'));

    $cache->deleteByTag('users');

    $this->assertFalse($cache->has('user:1'));
    $this->assertFalse($cache->has('user:2'));
    $this->assertTrue($cache->has('post:1'));
  }
}
