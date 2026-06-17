<?php

namespace Schorts\SharedKernel\DomainEvent;

use DateTimeImmutable;

abstract class DomainEvent
{
  protected array $payload;
  protected DomainEventMetadata $metadata;

  public function __construct(
    string $correlationId,
    array $payload,
    ?DomainEventMetadata $customMetadata = null
  ) {
    $this->payload = $payload;

    $generateId = function (): string {
      return (string) (microtime(true) * 1000) . '-' . substr(bin2hex(random_bytes(6)), 0, 11);
    };

    $this->metadata = $customMetadata ?? new DomainEventMetadata(
      id: $generateId(),
      occurredAt: new DateTimeImmutable(),
      correlationId: $correlationId,
      causationId: null,
      requestId: null,
      version: 1,
      userId: null,
      tenantId: null,
      retries: 0,
      headers: null,
      context: null
    );
  }

  abstract public function getEventName(): string;

  public function getId(): string
  {
    return $this->metadata->id;
  }

  public function getMetadata(): DomainEventMetadata
  {
    return $this->metadata;
  }

  public function toPrimitives(): DomainEventPrimitives
  {
    return new DomainEventPrimitives(
      id: $this->metadata->id,
      type: $this->getEventName(),
      occurredAt: $this->metadata->occurredAt->format(DateTimeImmutable::ATOM),
      correlationId: $this->metadata->correlationId,
      causationId: $this->metadata->causationId,
      requestId: $this->metadata->requestId,
      version: $this->metadata->version,
      userId: $this->metadata->userId,
      tenantId: $this->metadata->tenantId,
      payload: $this->payload,
      meta: [
        'retries' => $this->metadata->retries,
        'headers' => $this->metadata->headers,
        'context' => $this->metadata->context,
      ]
    );
  }

  public function setCorrelationId(string $correlationId): void
  {
    $this->metadata->correlationId = $correlationId;
  }

  public function setCausationId(?string $causationId): void
  {
    $this->metadata->causationId = $causationId;
  }

  public function setUserId(?string $userId): void
  {
    $this->metadata->userId = $userId;
  }

  public function setTenantId(?string $tenantId): void
  {
    $this->metadata->tenantId = $tenantId;
  }

  public function addHeaders(array $headers): void
  {
    $this->metadata->headers = array_merge($this->metadata->headers ?? [], $headers);
  }

  public function addContext(string $key, mixed $value): void
  {
    $this->metadata->context = array_merge($this->metadata->context ?? [], [$key => $value]);
  }

  public $ack = null;
  public $requeue = null;
}
