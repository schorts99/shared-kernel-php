<?php

declare(strict_types=1);

namespace Schorts\SharedKernel\CQRS\Command;

use DateTimeInterface;
use JsonSerializable;

final class CommandMetadata implements JsonSerializable
{
  public function __construct(
    public string $id,
    public DateTimeInterface $createdAt,
    public string $correlationId,
    public ?string $causationId,
    public ?string $requestId,
    public int $version,
    public ?string $userId,
    public ?string $tenantId,
    public ?array $headers,
    public ?array $context,
  ) {}

  /**
   * @return array<string, mixed>
   */
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

  public function jsonSerialize(): mixed
  {
    return $this->toArray();
  }
}
