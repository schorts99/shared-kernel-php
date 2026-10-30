<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\CQRS\Command;

interface Command
{
  public function getType(): string;

  public function getMetadata(): CommandMetadata;

  public function getPayload(): ?array;

  public function toPrimitives(): CommandPrimitives;
}
