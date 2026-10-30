<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\DomainEvent;

use DateTimeImmutable;
use JsonSerializable;

class DomainEventMetadata implements JsonSerializable
{
  public function __construct(
    public string $id,
    public DateTimeImmutable $occurredAt,
    public string $correlationId,
    public ?string $causationId,
    public ?string $requestId,
    public int $version,
    public ?string $userId,
    public ?string $tenantId,
    public int $retries,
    public ?array $headers = null,
    public ?array $context = null
  ) {}

  /**
   * @return array<string, mixed>
   */
  public function toArray(): array
  {
    return [
      'id'            => $this->id,
      'occurredAt'    => $this->occurredAt,
      'correlationId' => $this->correlationId,
      'causationId'   => $this->causationId,
      'requestId'     => $this->requestId,
      'version'       => $this->version,
      'userId'        => $this->userId,
      'tenantId'      => $this->tenantId,
      'retries'       => $this->retries,
      'headers'       => $this->headers,
      'context'       => $this->context,
    ];
  }

  public function jsonSerialize(): mixed
  {
    return $this->toArray();
  }
}
