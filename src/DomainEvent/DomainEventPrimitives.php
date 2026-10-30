<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\DomainEvent;

use JsonSerializable;

class DomainEventPrimitives implements JsonSerializable
{
  public string $id;
  public string $type;
  public string $occurredAt;
  public string $correlationId;
  public ?string $causationId;
  public ?string $requestId;
  public int $version;
  public ?string $userId;
  public ?string $tenantId;
  /** @var array<string,mixed> */
  public array $payload;
  /** @var array<string,mixed> */
  public array $meta;

  public function __construct(
    string $id,
    string $type,
    string $occurredAt,
    string $correlationId,
    ?string $causationId,
    ?string $requestId,
    int $version,
    ?string $userId,
    ?string $tenantId,
    array $payload,
    array $meta
  ) {
    $this->id = $id;
    $this->type = $type;
    $this->occurredAt = $occurredAt;
    $this->correlationId = $correlationId;
    $this->causationId = $causationId;
    $this->requestId = $requestId;
    $this->version = $version;
    $this->userId = $userId;
    $this->tenantId = $tenantId;
    $this->payload = $payload;
    $this->meta = $meta;
  }

  /**
   * @return array<string,mixed>
   */
  public function toArray(): array
  {
    return [
      'id' => $this->id,
      'type' => $this->type,
      'occurredAt' => $this->occurredAt,
      'correlationId' => $this->correlationId,
      'causationId' => $this->causationId,
      'requestId' => $this->requestId,
      'version' => $this->version,
      'userId' => $this->userId,
      'tenantId' => $this->tenantId,
      'payload' => $this->payload,
      'meta' => $this->meta,
    ];
  }

  public function jsonSerialize(): mixed
  {
    return $this->toArray();
  }
}
