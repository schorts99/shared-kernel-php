<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\Tests\Result;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Schorts\SharedKernel\Result\Result;

final class ResultTest extends TestCase
{
  public function testSuccessResult(): void
  {
    $result = Result::success('data');

    $this->assertTrue($result->isSuccess());
    $this->assertFalse($result->isFailure());
    $this->assertSame('data', $result->getValue());
    $this->assertSame('data', $result->unwrap());
    $this->assertSame('data', $result->unwrapOr('default'));
  }

  public function testFailureResult(): void
  {
    $result = Result::failure('error_code');

    $this->assertFalse($result->isSuccess());
    $this->assertTrue($result->isFailure());
    $this->assertSame('error_code', $result->getError());
    $this->assertSame('default', $result->unwrapOr('default'));

    $this->expectException(RuntimeException::class);
    $result->getValue();
  }

  public function testFromCallable(): void
  {
    $success = Result::fromCallable(static fn (): int => 42);
    $this->assertTrue($success->isSuccess());
    $this->assertSame(42, $success->getValue());

    $failure = Result::fromCallable(static function (): never {
      throw new RuntimeException('Boom');
    });
    $this->assertTrue($failure->isFailure());
    $this->assertInstanceOf(RuntimeException::class, $failure->getError());
  }

  public function testMapAndFlatMap(): void
  {
    $res = Result::success(10)
      ->map(static fn (int $x): int => $x * 2)
      ->flatMap(static fn (int $x): Result => Result::success($x + 5));

    $this->assertSame(25, $res->getValue());
  }

  public function testCombine(): void
  {
    $allOk = Result::combine([
      Result::success(1),
      Result::success(2),
      Result::success(3),
    ]);

    $this->assertTrue($allOk->isSuccess());
    $this->assertSame([1, 2, 3], $allOk->getValue());

    $oneFailed = Result::combine([
      Result::success(1),
      Result::failure('error_in_2'),
      Result::success(3),
    ]);

    $this->assertTrue($oneFailed->isFailure());
    $this->assertSame('error_in_2', $oneFailed->getError());
  }

  public function testToAndFromPrimitives(): void
  {
    $success = Result::success(['key' => 'value']);
    $primitives = $success->toPrimitives();
    $reconstructed = Result::fromPrimitives($primitives);

    $this->assertTrue($reconstructed->isSuccess());
    $this->assertSame(['key' => 'value'], $reconstructed->getValue());

    $failure = Result::failure(new RuntimeException('Something failed', 500));
    $failPrimitives = $failure->toPrimitives();
    $failReconstructed = Result::fromPrimitives($failPrimitives);

    $this->assertTrue($failReconstructed->isFailure());
    $this->assertInstanceOf(RuntimeException::class, $failReconstructed->getError());
    $this->assertSame('Something failed', $failReconstructed->getError()->getMessage());
  }
}
