<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\Tests\EventBus;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Schorts\SharedKernel\DomainEvent\DomainEvent;
use Schorts\SharedKernel\DomainEvent\DomainEventRegistry;
use Schorts\SharedKernel\EventBus\AbstractEventSubscriber;
use Schorts\SharedKernel\EventBus\EventSubscriberContext;
use Schorts\SharedKernel\EventBus\EventSubscriberOptions;
use Schorts\SharedKernel\EventBus\InMemoryDeadLetterStore;
use Schorts\SharedKernel\EventBus\InMemoryEventBus;
use Schorts\SharedKernel\EventBus\InMemoryEventStore;

class OrderPlacedEvent extends DomainEvent
{
  public function getEventName(): string
  {
    return 'order.placed';
  }
}

class SendConfirmationEmailSubscriber extends AbstractEventSubscriber
{
  public array $receivedEvents = [];

  public function subscribedTo(): array
  {
    return ['order.placed'];
  }

  public function handle(DomainEvent $event, ?EventSubscriberContext $context = null): void
  {
    $this->receivedEvents[] = $event;
  }
}

class FailingSubscriber extends AbstractEventSubscriber
{
  public int $attempts = 0;

  public function subscribedTo(): array
  {
    return ['order.placed'];
  }

  public function handle(DomainEvent $event, ?EventSubscriberContext $context = null): void
  {
    $this->attempts++;
    throw new RuntimeException('Subscriber failure');
  }
}

final class EventBusTest extends TestCase
{
  public function testPublishAndSubscribe(): void
  {
    $eventStore = new InMemoryEventStore();
    $bus = new InMemoryEventBus(eventStore: $eventStore);

    $subscriber = new SendConfirmationEmailSubscriber();
    $bus->registerSubscriber($subscriber);

    $this->assertTrue($bus->hasSubscribers('order.placed'));

    $event = new OrderPlacedEvent('corr-order-1', ['orderId' => 'ord-100', 'amount' => 99.99]);
    $bus->publish($event);

    $this->assertCount(1, $subscriber->receivedEvents);
    $this->assertSame('order.placed', $subscriber->receivedEvents[0]->getEventName());
    $this->assertSame('ord-100', $subscriber->receivedEvents[0]->getPayload()['orderId']);

    $this->assertCount(1, $eventStore->all());
  }

  public function testRetryAndDeadLetterStore(): void
  {
    $deadLetterStore = new InMemoryDeadLetterStore();
    $bus = new InMemoryEventBus(deadLetterStore: $deadLetterStore);

    $failingSubscriber = new FailingSubscriber(new EventSubscriberOptions(
      retryOnError: true,
      maxRetries: 2,
    ));
    $bus->registerSubscriber($failingSubscriber);

    $event = new OrderPlacedEvent('corr-order-2', ['orderId' => 'ord-200']);
    $bus->publish($event);

    // 1 initial attempt + 2 retries = 3 attempts
    $this->assertSame(3, $failingSubscriber->attempts);

    $failedEntries = $deadLetterStore->getFailedEvents();
    $this->assertCount(1, $failedEntries);
    $this->assertSame('Subscriber failure', $failedEntries[0]['error']);
  }

  public function testDomainEventRegistry(): void
  {
    DomainEventRegistry::clear();
    $this->assertFalse(DomainEventRegistry::has('order.placed'));

    DomainEventRegistry::register('order.placed', OrderPlacedEvent::class);
    $this->assertTrue(DomainEventRegistry::has('order.placed'));

    $event = new OrderPlacedEvent('corr-reg-1', ['id' => '123']);
    $primitives = $event->toPrimitives();

    $reconstituted = DomainEventRegistry::fromPrimitives($primitives);
    $this->assertInstanceOf(OrderPlacedEvent::class, $reconstituted);
    $this->assertSame('order.placed', $reconstituted->getEventName());
    $this->assertSame('corr-reg-1', $reconstituted->getCorrelationId());
  }
}
