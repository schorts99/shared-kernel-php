<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\EventBus;

use Schorts\SharedKernel\DomainEvent\DomainEvent;

interface EventBus
{
  public function subscribe(string $eventName, EventSubscriber $subscriber): void;

  public function unsubscribe(string $eventName, EventSubscriber $subscriber): bool;

  public function publish(DomainEvent $event): void;

  /**
   * @param array<DomainEvent> $events
   */
  public function publishMany(array $events): void;

  public function use(EventBusMiddleware $middleware): void;

  public function removeMiddleware(EventBusMiddleware $middleware): bool;

  public function hasSubscribers(string $eventName): bool;

  /**
   * @return array<string>
   */
  public function getSubscribedEvents(): array;

  public function clear(): void;
}
