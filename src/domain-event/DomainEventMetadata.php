<?php

namespace Schorts\SharedKernel\DomainEvent;

class DomainEventMetadata
{
  public string $id;
  public \DateTimeImmutable $occurredAt;
  public string $correlationId;
  public ?string $causationId;
  public ?string $requestId;
  public int $version;
  public ?string $userId;
  public ?string $tenantId;
  public int $retries;
  public ?array $headers;
  public ?array $context;

  public function __construct(
    string $id,
    \DateTimeImmutable $occurredAt,
    string $correlationId,
    ?string $causationId,
    ?string $requestId,
    int $version,
    ?string $userId,
    ?string $tenantId,
    int $retries,
    ?array $headers = null,
    ?array $context = null
  ) {
    $this->id = $id;
    $this->occurredAt = $occurredAt;
    $this->correlationId = $correlationId;
    $this->causationId = $causationId;
    $this->requestId = $requestId;
    $this->version = $version;
    $this->userId = $userId;
    $this->tenantId = $tenantId;
    $this->retries = $retries;
    $this->headers = $headers;
    $this->context = $context;
  }
}
