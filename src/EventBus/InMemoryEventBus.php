<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\EventBus;

use DateTimeImmutable;
use Schorts\SharedKernel\DomainEvent\DomainEvent;
use Schorts\SharedKernel\Logger\Logger;
use Throwable;

class InMemoryEventBus implements EventBus
{
  /** @var array<string, array<EventSubscriber>> */
  private array $subscribers = [];
  /** @var array<EventBusMiddleware> */
  private array $middleware = [];
  private EventBusConfig $config;
  private ?EventStore $eventStore;
  private ?DeadLetterStore $deadLetterStore;
  private ?Logger $logger;

  public function __construct(
    ?EventBusConfig $config = null,
    ?EventStore $eventStore = null,
    ?DeadLetterStore $deadLetterStore = null,
    ?Logger $logger = null,
  ) {
    $this->config = $config ?? new EventBusConfig();
    $this->eventStore = $eventStore;
    $this->deadLetterStore = $deadLetterStore;
    $this->logger = $logger;
  }

  public function subscribe(string $eventName, EventSubscriber $subscriber): void
  {
    if (!isset($this->subscribers[$eventName])) {
      $this->subscribers[$eventName] = [];
    }

    $this->subscribers[$eventName][] = $subscriber;

    // Sort by subscriber priority descending
    usort($this->subscribers[$eventName], static function (EventSubscriber $a, EventSubscriber $b): int {
      return $b->getOptions()->priority <=> $a->getOptions()->priority;
    });
  }

  public function registerSubscriber(EventSubscriber $subscriber): void
  {
    foreach ($subscriber->subscribedTo() as $eventName) {
      $this->subscribe($eventName, $subscriber);
    }
  }

  public function unsubscribe(string $eventName, EventSubscriber $subscriber): bool
  {
    if (!isset($this->subscribers[$eventName])) {
      return false;
    }

    $initialCount = count($this->subscribers[$eventName]);
    $this->subscribers[$eventName] = array_values(array_filter(
      $this->subscribers[$eventName],
      static fn (EventSubscriber $s): bool => $s !== $subscriber
    ));

    return count($this->subscribers[$eventName]) < $initialCount;
  }

  public function hasSubscribers(string $eventName): bool
  {
    return !empty($this->subscribers[$eventName]);
  }

  public function getSubscribedEvents(): array
  {
    return array_keys($this->subscribers);
  }

  public function use(EventBusMiddleware $middleware): void
  {
    $this->middleware[] = $middleware;
  }

  public function removeMiddleware(EventBusMiddleware $middleware): bool
  {
    $key = array_search($middleware, $this->middleware, true);
    if ($key === false) {
      return false;
    }

    array_splice($this->middleware, (int) $key, 1);
    return true;
  }

  public function publish(DomainEvent $event): void
  {
    $this->eventStore?->append($event);

    $eventName = $event->getEventName();
    $subscribers = $this->subscribers[$eventName] ?? [];

    $startTime = new DateTimeImmutable();
    $context = new EventBusContext(
      startTime: $startTime,
      metadata: $event->getMetadata()->toArray(),
      config: $this->config,
      correlationId: $event->getCorrelationId(),
    );

    foreach ($this->middleware as $mw) {
      $mw->beforePublish($event, $context);
    }

    foreach ($subscribers as $subscriber) {
      $this->dispatchToSubscriber($subscriber, $event, $context);
    }

    foreach ($this->middleware as $mw) {
      $mw->afterPublish($event, $context);
    }
  }

  public function publishMany(array $events): void
  {
    foreach ($events as $event) {
      $this->publish($event);
    }
  }

  private function dispatchToSubscriber(
    EventSubscriber $subscriber,
    DomainEvent $event,
    EventBusContext $busContext,
  ): void {
    $options = $subscriber->getOptions();
    $maxRetries = $options->retryOnError ? ($options->maxRetries > 0 ? $options->maxRetries : $this->config->maxRetries) : 0;
    $attempts = 0;
    $maxAttempts = $maxRetries + 1;

    $subscriberContext = new EventSubscriberContext(
      subscriptionTime: new DateTimeImmutable(),
      metadata: $busContext->metadata,
      options: $options,
      correlationId: $busContext->correlationId,
    );

    while ($attempts < $maxAttempts) {
      $attempts++;
      try {
        $subscriber->handle($event, $subscriberContext);
        return;
      } catch (Throwable $e) {
        $this->logger?->error(sprintf(
          'Error handling event %s in subscriber %s (attempt %d/%d): %s',
          $event->getEventName(),
          $subscriber->getName(),
          $attempts,
          $maxAttempts,
          $e->getMessage()
        ));

        if ($attempts >= $maxAttempts) {
          foreach ($this->middleware as $mw) {
            $mw->onError($event, $e, $busContext);
          }

          $this->deadLetterStore?->store($event, $e, $subscriber->getName());
          return;
        }
      }
    }
  }

  public function clear(): void
  {
    $this->subscribers = [];
    $this->middleware = [];
    $this->eventStore?->clear();
    $this->deadLetterStore?->clear();
  }
}
