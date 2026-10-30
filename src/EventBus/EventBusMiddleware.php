<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\EventBus;

use Schorts\SharedKernel\DomainEvent\DomainEvent;
use Throwable;

interface EventBusMiddleware
{
  public function beforePublish(DomainEvent $event, EventBusContext $context): void;

  public function afterPublish(DomainEvent $event, EventBusContext $context): void;

  public function onError(DomainEvent $event, Throwable $error, EventBusContext $context): void;
}
