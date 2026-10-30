<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\Tests\Criteria;

use PHPUnit\Framework\TestCase;
use Schorts\SharedKernel\Criteria\Criteria;
use Schorts\SharedKernel\Criteria\Direction;
use Schorts\SharedKernel\Criteria\Exceptions\LimitNotValid;
use Schorts\SharedKernel\Criteria\Exceptions\OffsetNotValid;
use Schorts\SharedKernel\Criteria\Operator;

final class CriteriaTest extends TestCase
{
  public function testCriteriaFluentBuilding(): void
  {
    $criteria = Criteria::none()
      ->whereEqual('status', 'active')
      ->whereGreaterThan('age', 18)
      ->whereIn('role', ['admin', 'editor'])
      ->orderBy('created_at', Direction::DESC)
      ->limitResults(10)
      ->offsetResults(0); // 0-based offset should now succeed!

    $this->assertTrue($criteria->hasFilters());
    $this->assertTrue($criteria->hasOrders());
    $this->assertCount(3, $criteria->filters());
    $this->assertCount(1, $criteria->orders());
    $this->assertSame(10, $criteria->limit());
    $this->assertSame(0, $criteria->offset());

    $array = $criteria->toArray();
    $this->assertSame('status', $array['filters'][0]['field']);
    $this->assertSame('EQUAL', $array['filters'][0]['operator']);
    $this->assertSame('active', $array['filters'][0]['value']);
    $this->assertSame('DESC', $array['orders'][0]['direction']);
  }

  public function testOffsetValidation(): void
  {
    $this->expectException(OffsetNotValid::class);
    Criteria::none()->offsetResults(-1);
  }

  public function testLimitValidation(): void
  {
    $this->expectException(LimitNotValid::class);
    Criteria::none()->limitResults(0);
  }
}
