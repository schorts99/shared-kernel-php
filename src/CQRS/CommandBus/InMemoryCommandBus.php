<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\CQRS\CommandBus;

use DateTimeImmutable;
use Schorts\SharedKernel\CQRS\Command\Command;
use Schorts\SharedKernel\CQRS\Command\CommandRegistry;
use Schorts\SharedKernel\CQRS\Command\Exceptions\CommandAlreadyRegistered;
use Schorts\SharedKernel\CQRS\Command\Exceptions\CommandHandlerNotRegistered;
use Schorts\SharedKernel\CQRS\CommandHandler\CommandHandler;
use Schorts\SharedKernel\CQRS\CommandHandler\CommandHandlerContext;
use Schorts\SharedKernel\EventBus\EventBus;
use Schorts\SharedKernel\Exceptions\AggregateError;
use Schorts\SharedKernel\UnitOfWork\UnitOfWork;
use Throwable;

class InMemoryCommandBus implements CommandBus
{
  /** @var array<string, CommandHandler> */
  private array $handlers = [];
  /** @var array<CommandBusMiddleware> */
  private array $middleware = [];
  private CommandBusConfig $config;
  private ?EventBus $eventBus;
  private ?UnitOfWork $unitOfWork;

  public function __construct(
    ?CommandBusConfig $config = null,
    ?EventBus $eventBus = null,
    ?UnitOfWork $unitOfWork = null,
  ) {
    $this->config = $config ?? new CommandBusConfig();
    $this->eventBus = $eventBus;
    $this->unitOfWork = $unitOfWork;
  }

  public function register(string $type, CommandHandler $handler): void
  {
    if (isset($this->handlers[$type])) {
      throw new CommandAlreadyRegistered($type);
    }

    $this->handlers[$type] = $handler;
  }

  public function unregister(string $type): bool
  {
    if (!isset($this->handlers[$type])) {
      return false;
    }

    unset($this->handlers[$type]);
    return true;
  }

  public function hasHandler(string $type): bool
  {
    return isset($this->handlers[$type]);
  }

  public function getRegisteredTypes(): array
  {
    return array_keys($this->handlers);
  }

  public function getConfig(): CommandBusConfig
  {
    return $this->config;
  }

  public function use(CommandBusMiddleware $middleware): void
  {
    $this->middleware[] = $middleware;
  }

  public function removeMiddleware(CommandBusMiddleware $middleware): bool
  {
    $key = array_search($middleware, $this->middleware, true);
    if ($key === false) {
      return false;
    }

    array_splice($this->middleware, (int) $key, 1);
    return true;
  }

  public function dispatch(Command $command): mixed
  {
    $type = $command->getType();
    $handler = $this->handlers[$type] ?? null;

    if ($handler === null) {
      throw new CommandHandlerNotRegistered($type);
    }

    $primitives = $command->toPrimitives();
    $deserializedCommand = CommandRegistry::has($primitives->type)
      ? CommandRegistry::fromPrimitives($primitives)
      : $command;

    $startTime = new DateTimeImmutable();
    $correlationId = $deserializedCommand->getMetadata()->correlationId;

    $context = new CommandBusContext(
      correlationId: $correlationId,
      startTime: $startTime,
      metadata: $deserializedCommand->getMetadata()->toArray(),
      config: $this->config,
    );

    $handlerContext = new CommandHandlerContext(
      metadata: $deserializedCommand->getMetadata(),
      options: $handler->getOptions(),
      startTime: $startTime,
      correlationId: $correlationId,
      unitOfWork: $this->unitOfWork,
    );

    try {
      foreach ($this->middleware as $mw) {
        $mw->beforeDispatch($deserializedCommand, $context);
      }

      if ($this->config->transactional && $this->unitOfWork !== null) {
        $this->unitOfWork->begin();
      }

      $result = $handler->handle($deserializedCommand, $handlerContext);

      if ($this->config->transactional && $this->unitOfWork !== null) {
        $this->unitOfWork->commit();
      }

      foreach ($this->middleware as $mw) {
        $mw->afterDispatch($deserializedCommand, $result, $context);
      }

      if ($this->config->autoPublishEvents && $this->eventBus !== null && !empty($handlerContext->events)) {
        $this->eventBus->publishMany($handlerContext->events);
      }

      return $result;
    } catch (Throwable $e) {
      if ($this->config->transactional && $this->unitOfWork !== null) {
        $this->unitOfWork->rollback();
      }

      foreach ($this->middleware as $mw) {
        $mw->onError($deserializedCommand, $e, $context);
      }

      throw $e;
    }
  }

  public function dispatchMany(array $commands): array
  {
    $results = [];
    $errors = [];

    foreach ($commands as $command) {
      try {
        $results[] = $this->dispatch($command);
      } catch (Throwable $e) {
        $errors[] = $e;
      }
    }

    if (!empty($errors)) {
      throw new AggregateError('One or more commands failed during bulk dispatch.', $errors);
    }

    return $results;
  }

  public function clear(): void
  {
    $this->handlers = [];
    $this->middleware = [];
  }
}
