<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\CQRS\CommandHandler;

use DateTimeImmutable;
use Schorts\SharedKernel\CQRS\Command\CommandMetadata;
use Schorts\SharedKernel\DomainEvent\DomainEvent;
use Schorts\SharedKernel\UnitOfWork\UnitOfWork;

final class CommandHandlerContext
{
  /**
   * @param array<DomainEvent> $events
   */
  public function __construct(
    public readonly CommandMetadata $metadata,
    public readonly CommandHandlerOptions $options,
    public readonly DateTimeImmutable $startTime,
    public readonly string $correlationId,
    public array $events = [],
    public readonly ?UnitOfWork $unitOfWork = null,
  ) {}
}
