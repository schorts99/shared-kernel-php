<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\CQRS\CommandHandler;

use Schorts\SharedKernel\CQRS\Command\Command;

interface CommandHandler
{
  public function handle(Command $command, ?CommandHandlerContext $context = null): mixed;

  public function getOptions(): CommandHandlerOptions;

  public function validate(Command $command): void;

  public function authorize(Command $command): void;
}
