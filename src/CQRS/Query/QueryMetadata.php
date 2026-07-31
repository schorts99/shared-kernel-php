<?php

namespace Schorts\SharedKernel\CQRS\Query;

final class QueryMetadata
{
  public function __construct(
    public string $id,
    public \DateTimeInterface $createdAt,
    public string $correlationId,
    public ?string $causationId,
    public ?string $requestId,
    public int $version,
    public ?string $userId,
    public ?string $tenantId,
    public ?array $headers,
    public ?array $context,
  ) {}

  public function toArray(): array
  {
    return [
      'id'            => $this->id,
      'createdAt'     => $this->createdAt,
      'correlationId' => $this->correlationId,
      'causationId'   => $this->causationId,
      'requestId'     => $this->requestId,
      'version'       => $this->version,
      'userId'        => $this->userId,
      'tenantId'      => $this->tenantId,
      'headers'       => $this->headers,
      'context'       => $this->context,
    ];
  }
}
