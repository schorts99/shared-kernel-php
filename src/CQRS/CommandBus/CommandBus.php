<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\CQRS\CommandBus;

use Schorts\SharedKernel\CQRS\Command\Command;
use Schorts\SharedKernel\CQRS\CommandHandler\CommandHandler;

interface CommandBus
{
  public function register(string $type, CommandHandler $handler): void;

  public function unregister(string $type): bool;

  public function hasHandler(string $type): bool;

  /**
   * @return array<string>
   */
  public function getRegisteredTypes(): array;

  public function dispatch(Command $command): mixed;

  /**
   * @param array<Command> $commands
   * @return array<mixed>
   */
  public function dispatchMany(array $commands): array;

  public function use(CommandBusMiddleware $middleware): void;

  public function removeMiddleware(CommandBusMiddleware $middleware): bool;

  public function getConfig(): CommandBusConfig;

  public function clear(): void;
}
