<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\CQRS\CommandBus;

use Schorts\SharedKernel\CQRS\Command\Command;
use Throwable;

interface CommandBusMiddleware
{
  public function beforeDispatch(Command $command, CommandBusContext $context): void;

  public function afterDispatch(Command $command, mixed $result, CommandBusContext $context): void;

  public function onError(Command $command, Throwable $error, CommandBusContext $context): void;
}
