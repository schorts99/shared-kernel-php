<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\Tests\CQRS;

use PHPUnit\Framework\TestCase;
use Schorts\SharedKernel\CQRS\Query\AbstractQuery;
use Schorts\SharedKernel\CQRS\Query\Query;
use Schorts\SharedKernel\CQRS\QueryBus\InMemoryQueryBus;
use Schorts\SharedKernel\CQRS\QueryBus\QueryBusContext;
use Schorts\SharedKernel\CQRS\QueryBus\QueryBusMiddleware;
use Schorts\SharedKernel\CQRS\QueryHandler\AbstractQueryHandler;
use Schorts\SharedKernel\CQRS\QueryHandler\QueryHandlerContext;

class GetUserQuery extends AbstractQuery
{
  public function getType(): string
  {
    return 'get.user';
  }
}

class GetUserQueryHandler extends AbstractQueryHandler
{
  public function execute(Query $query, ?QueryHandlerContext $context = null): mixed
  {
    return [
      'id' => $query->getPayload()['id'] ?? 'unknown',
      'name' => 'John Doe',
    ];
  }
}

final class QueryBusTest extends TestCase
{
  public function testDispatchQuery(): void
  {
    $bus = new InMemoryQueryBus();
    $handler = new GetUserQueryHandler();
    $bus->register('get.user', $handler);

    $this->assertTrue($bus->hasHandler('get.user'));
    $this->assertFalse($bus->hasHandler('other.query'));

    $query = new GetUserQuery('corr-123', ['id' => 'u-1']);
    $result = $bus->dispatch($query);

    $this->assertSame(['id' => 'u-1', 'name' => 'John Doe'], $result);
  }

  public function testMiddleware(): void
  {
    $bus = new InMemoryQueryBus();
    $handler = new GetUserQueryHandler();
    $bus->register('get.user', $handler);

    $events = [];

    $middleware = new class($events) implements QueryBusMiddleware {
      public function __construct(private array &$events) {}
      public function beforeDispatch(Query $query, QueryBusContext $context): void {
        $this->events[] = 'before';
      }
      public function afterDispatch(Query $query, mixed $result, QueryBusContext $context): void {
        $this->events[] = 'after';
      }
      public function onError(Query $query, \Throwable $error, QueryBusContext $context): void {
        $this->events[] = 'error';
      }
    };

    $bus->use($middleware);

    $query = new GetUserQuery('corr-456', ['id' => 'u-2']);
    $bus->dispatch($query);

    $this->assertSame(['before', 'after'], $events);
  }

  public function testDispatchMany(): void
  {
    $bus = new InMemoryQueryBus();
    $handler = new GetUserQueryHandler();
    $bus->register('get.user', $handler);

    $queries = [
      new GetUserQuery('corr-1', ['id' => 'u-1']),
      new GetUserQuery('corr-2', ['id' => 'u-2']),
    ];

    $results = $bus->dispatchMany($queries);

    $this->assertCount(2, $results);
    $this->assertSame('u-1', $results[0]['id']);
    $this->assertSame('u-2', $results[1]['id']);
  }
}
