<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\EventBus;

use Schorts\SharedKernel\DomainEvent\DomainEvent;

abstract class AbstractEventSubscriber implements EventSubscriber
{
  protected readonly EventSubscriberOptions $options;

  public function __construct(?EventSubscriberOptions $options = null)
  {
    $this->options = $options ?? new EventSubscriberOptions();
  }

  abstract public function handle(DomainEvent $event, ?EventSubscriberContext $context = null): void;

  abstract public function subscribedTo(): array;

  public function getName(): string
  {
    return static::class;
  }

  public function getOptions(): EventSubscriberOptions
  {
    return $this->options;
  }
}
