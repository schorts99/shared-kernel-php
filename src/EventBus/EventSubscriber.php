<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\EventBus;

use Schorts\SharedKernel\DomainEvent\DomainEvent;

interface EventSubscriber
{
  /**
   * @param DomainEvent $event
   * @param EventSubscriberContext|null $context
   */
  public function handle(DomainEvent $event, ?EventSubscriberContext $context = null): void;

  /**
   * @return array<string> List of event names this subscriber listens to
   */
  public function subscribedTo(): array;

  /**
   * Stable delivery identity, unique among subscribers to the same event.
   */
  public function getName(): string;

  public function getOptions(): EventSubscriberOptions;
}
