<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\Tests\CQRS;

use PHPUnit\Framework\TestCase;
use Schorts\SharedKernel\CQRS\Command\AbstractCommand;
use Schorts\SharedKernel\CQRS\Command\Command;
use Schorts\SharedKernel\CQRS\Command\CommandRegistry;
use Schorts\SharedKernel\CQRS\CommandBus\CommandBusConfig;
use Schorts\SharedKernel\CQRS\CommandBus\CommandBusContext;
use Schorts\SharedKernel\CQRS\CommandBus\CommandBusMiddleware;
use Schorts\SharedKernel\CQRS\CommandBus\InMemoryCommandBus;
use Schorts\SharedKernel\CQRS\CommandHandler\AbstractCommandHandler;
use Schorts\SharedKernel\CQRS\CommandHandler\CommandHandlerContext;
use Schorts\SharedKernel\CQRS\CommandHandler\CommandHandlerOptions;
use Schorts\SharedKernel\CQRS\CommandHandler\Exceptions\CommandAuthorizationError;
use Schorts\SharedKernel\CQRS\CommandHandler\Exceptions\CommandValidationError;
use Schorts\SharedKernel\Idempotency\InMemoryIdempotencyStore;

class CreateUserCommand extends AbstractCommand
{
  public function getType(): string
  {
    return 'create.user';
  }
}

class CreateUserCommandHandler extends AbstractCommandHandler
{
  public int $executionCount = 0;

  public function validate(Command $command): void
  {
    $payload = $command->getPayload();
    if (empty($payload['email'])) {
      throw new CommandValidationError($command->getType(), 'Email is required.');
    }
  }

  public function authorize(Command $command): void
  {
    $headers = $command->getMetadata()->headers;
    if (($headers['role'] ?? '') === 'banned') {
      throw new CommandAuthorizationError($command->getType(), 'Banned users cannot create accounts.');
    }
  }

  protected function execute(Command $command, CommandHandlerContext $context): mixed
  {
    $this->executionCount++;
    return [
      'id' => 'u-' . $this->executionCount,
      'email' => $command->getPayload()['email'],
    ];
  }
}

final class CommandBusTest extends TestCase
{
  public function testDispatchCommand(): void
  {
    $bus = new InMemoryCommandBus();
    $handler = new CreateUserCommandHandler();
    $bus->register('create.user', $handler);

    $this->assertTrue($bus->hasHandler('create.user'));
    $this->assertFalse($bus->hasHandler('delete.user'));

    $command = new CreateUserCommand('corr-1', ['email' => 'alice@example.com']);
    $result = $bus->dispatch($command);

    $this->assertSame(['id' => 'u-1', 'email' => 'alice@example.com'], $result);
    $this->assertSame(1, $handler->executionCount);
  }

  public function testCommandValidation(): void
  {
    $bus = new InMemoryCommandBus();
    $handler = new CreateUserCommandHandler();
    $bus->register('create.user', $handler);

    $this->expectException(CommandValidationError::class);
    $this->expectExceptionMessage('Email is required.');

    $command = new CreateUserCommand('corr-2', ['email' => '']);
    $bus->dispatch($command);
  }

  public function testCommandAuthorization(): void
  {
    $bus = new InMemoryCommandBus();
    $handler = new CreateUserCommandHandler();
    $bus->register('create.user', $handler);

    $this->expectException(CommandAuthorizationError::class);
    $this->expectExceptionMessage('Banned users cannot create accounts.');

    $command = new CreateUserCommand('corr-3', ['email' => 'bob@example.com'], [
      'headers' => ['role' => 'banned'],
    ]);
    $bus->dispatch($command);
  }

  public function testIdempotency(): void
  {
    $idempotencyStore = new InMemoryIdempotencyStore();
    $options = new CommandHandlerOptions(
      idempotent: true,
      idempotencyStore: $idempotencyStore,
    );

    $handler = new CreateUserCommandHandler($options);
    $bus = new InMemoryCommandBus();
    $bus->register('create.user', $handler);

    $command = new CreateUserCommand('corr-4', ['email' => 'charlie@example.com'], [
      'id' => 'fixed-cmd-id-123',
    ]);

    $firstResult = $bus->dispatch($command);
    $secondResult = $bus->dispatch($command);

    $this->assertSame($firstResult, $secondResult);
    $this->assertSame(1, $handler->executionCount);
  }

  public function testMiddlewareExecution(): void
  {
    $bus = new InMemoryCommandBus();
    $handler = new CreateUserCommandHandler();
    $bus->register('create.user', $handler);

    $events = [];
    $middleware = new class($events) implements CommandBusMiddleware {
      public function __construct(private array &$events) {}
      public function beforeDispatch(Command $command, CommandBusContext $context): void {
        $this->events[] = 'before:' . $command->getType();
      }
      public function afterDispatch(Command $command, mixed $result, CommandBusContext $context): void {
        $this->events[] = 'after:' . $command->getType();
      }
      public function onError(Command $command, \Throwable $error, CommandBusContext $context): void {
        $this->events[] = 'error:' . $command->getType();
      }
    };

    $bus->use($middleware);

    $command = new CreateUserCommand('corr-5', ['email' => 'dave@example.com']);
    $bus->dispatch($command);

    $this->assertSame(['before:create.user', 'after:create.user'], $events);
  }
}
